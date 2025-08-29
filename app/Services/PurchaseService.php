<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Transaction;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private PurchaseRepositoryInterface $purchaseRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            // Calculate totals
            $purchase = new Purchase($data);
            $purchase->calculateTotals();
            
            // Generate reference number
            $purchase->reference_no = $this->generatePurchaseReference();
            // Save purchase
            $purchase = $this->purchaseRepository->create($purchase->toArray());
            
            // Create purchase transaction
            $this->createPurchaseTransaction($purchase);
            
            return $purchase;
        });
    }

    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {
            // Update purchase data
            $purchase->fill($data);
            $purchase->calculateTotals();
            
            $purchase = $this->purchaseRepository->update($purchase, $purchase->toArray());
            
            // Update the purchase transaction
            $this->updatePurchaseTransaction($purchase);
            
            return $purchase;
        });
    }

    public function confirmPurchase(Purchase $purchase): Purchase
    {
        $purchase->status = Purchase::STATUS_CONFIRMED;
        return $this->purchaseRepository->update($purchase, ['status' => Purchase::STATUS_CONFIRMED]);
    }

    public function cancelPurchase(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            // Cancel the purchase
            $purchase = $this->purchaseRepository->update($purchase, ['status' => Purchase::STATUS_CANCELLED]);
            
            // Reverse the purchase transaction
            $this->reversePurchaseTransaction($purchase);
            
            return $purchase;
        });
    }

    public function createRecordPayment(Purchase $purchase, float $amount, string $details = null): Transaction
    {
        return DB::transaction(function () use ($purchase, $amount, $details) {
            // Create payment transaction
            $transaction = $this->transactionRepository->create([
                'transactionable_type' => Purchase::class,
                'transactionable_id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'transaction_type' => Transaction::TYPE_PAYMENT_OUT,
                'reference_no' => $purchase->reference_no,
                'details' => $details ?: "Payment for purchase {$purchase->reference_no}",
                'amount' => -$amount, // Negative for payment out
                'balance' => $this->calculateSupplierBalance($purchase->user_id),
            ]);

            // Update purchase status
            $purchase->updateStatus();
            $this->purchaseRepository->update($purchase, ['status' => $purchase->status]);

            return $transaction;
        });
    }

    public function getSupplierLedger(int $supplierId, $startDate, $endDate): array
    {
        $transactions = $this->transactionRepository->getUserLedger($supplierId, $startDate, $endDate);
        $statistics = $this->purchaseRepository->getSupplierStatistics($supplierId);
        $openingBalance = $this->transactionRepository->getOpeningBalance($supplierId, $startDate);
        
        return [
            'transactions' => $transactions,
            'statistics' => $statistics,
            'opening_balance' => $openingBalance
        ];
    }

    public function getPurchaseStatistics(): array
    {
        return [
            'total_purchases' => $this->purchaseRepository->getTotalPurchases(),
            'total_paid' => $this->purchaseRepository->getTotalPaid(),
            'total_outstanding' => $this->purchaseRepository->getTotalOutstanding(),
            'unpaid_count' => $this->purchaseRepository->getUnpaidPurchases()->count(),
        ];
    }

    private function createPurchaseTransaction(Purchase $purchase): Transaction
    {
        return $this->transactionRepository->create([
            'user_id' => $purchase->user_id,
            'transactionable_type' => Purchase::class,
            'transactionable_id' => $purchase->id,
            'transaction_type' => Transaction::TYPE_PURCHASE,
            'amount' => $purchase->total_amount,
            'balance' => $this->calculateSupplierBalance($purchase->user_id),
            'details' => [
                'vehicle_number' => $purchase->vehicle_number,
                'weight_ton' => $purchase->weight_ton,
                'total_kg' => $purchase->total_kg,
                'total_cylinders' => $purchase->total_cylinders,
                'rate_11_8_kg' => $purchase->rate_11_8_kg,
                'reference_no' => $purchase->reference_no,
            ],
        ]);
    }

    private function updatePurchaseTransaction(Purchase $purchase): void
    {
        $transaction = $this->transactionRepository->findByTransactionable(Purchase::class, $purchase->id)
            ->where('transaction_type', Transaction::TYPE_PURCHASE)
            ->first();
        
        if ($transaction) {
            $this->transactionRepository->update($transaction, [
                'amount' => $purchase->total_amount,
                'details' => [
                    'vehicle_number' => $purchase->vehicle_number,
                    'weight_ton' => $purchase->weight_ton,
                    'total_kg' => $purchase->total_kg,
                    'total_cylinders' => $purchase->total_cylinders,
                    'rate_11_8_kg' => $purchase->rate_11_8_kg,
                    'reference_no' => $purchase->reference_no,
                ],
                'balance' => $this->calculateSupplierBalance($purchase->user_id),
            ]);
        }
    }

    private function reversePurchaseTransaction(Purchase $purchase): void
    {
        $transaction = $this->transactionRepository->findByTransactionable(Purchase::class, $purchase->id)
            ->where('transaction_type', Transaction::TYPE_PURCHASE)
            ->first();
        
        if ($transaction) {
            $this->transactionRepository->update($transaction, [
                'amount' => -$transaction->amount, // Reverse the amount
                'balance' => $this->calculateSupplierBalance($purchase->user_id),
            ]);
        }
    }

    private function calculateSupplierBalance(int $supplierId): float
    {
        return $this->purchaseRepository->getSupplierBalance($supplierId);
    }

    private function generatePurchaseReference(): string
    {
        $prefix = 'PUR';
        $year = date('Y');
        $month = date('m');
        
        // Get the last purchase number for this month
        $lastPurchase = Purchase::where('reference_no', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('reference_no', 'desc')
            ->first();
        
        if ($lastPurchase) {
            $lastSequence = (int) substr($lastPurchase->reference_no, -4);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}

