<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Sale;
use App\Models\Purchase;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private SaleRepositoryInterface $saleRepository,
        private PurchaseRepositoryInterface $purchaseRepository,
        private SaleService $saleService
    ) {}

    public function createPaymentIn(Sale $sale, array $data): Transaction
    {
        return DB::transaction(function () use ($sale, $data) {
            $transaction = $this->transactionRepository->create([
                'user_id' => $sale->user_id,
                'transactionable_type' => Sale::class,
                'transactionable_id' => $sale->id,
                'transaction_type' => Transaction::TYPE_PAYMENT_IN,
                'amount' => -$data['amount'], // Negative for payment in (reduces customer balance)
                'payment_method_id' => $data['method'],
                'balance' => $this->calculateCustomerBalance($sale->user_id),
                'details' => [
                    'note' => $data['note'],
                    'invoice_no' => $sale->invoice_no,
                    'reference' => $data['reference'],
                ],
            ]);
            
            $this->saleService->updateSaleStatus($sale);

            return $transaction;
        });
    }

    public function createPaymentOut(Purchase $purchase, float $amount, string $details = null): Transaction
    {
        return DB::transaction(function () use ($purchase, $amount, $details) {
            $transaction = $this->transactionRepository->create([
                'user_id' => $purchase->user_id,
                'transactionable_type' => Purchase::class,
                'transactionable_id' => $purchase->id,
                'transaction_type' => Transaction::TYPE_PAYMENT_OUT,
                'amount' => -$amount, // Negative for payment out (reduces supplier balance)
                'balance' => $this->calculateSupplierBalance($purchase->user_id),
                'details' => [
                    'payment_details' => $details,
                    'reference_no' => $purchase->reference_no,
                ],
            ]);

            // Update purchase status
            $purchase->updateStatus();
            $this->purchaseRepository->update($purchase, ['status' => $purchase->status]);

            return $transaction;
        });
    }

    public function payRemainingBalance(Sale $sale): Transaction
    {
        $remainingBalance = $sale->balance;
        
        if ($remainingBalance <= 0) {
            throw new \InvalidArgumentException('No remaining balance to pay.');
        }

        return $this->createPaymentIn($sale, $remainingBalance, 'Full payment for remaining balance');
    }

    public function payRemainingBalancePurchase(Purchase $purchase): Transaction
    {
        $remainingBalance = $purchase->balance;
        
        if ($remainingBalance <= 0) {
            throw new \InvalidArgumentException('No remaining balance to pay.');
        }

        return $this->createPaymentOut($purchase, $remainingBalance, 'Full payment for remaining balance');
    }

    public function getCustomerBalance(int $customerId): float
    {
        return $this->transactionRepository->sumByCustomer($customerId);
    }

    public function getSupplierBalance(int $supplierId): float
    {
        return $this->transactionRepository->sumByCustomer($supplierId); // Same method for suppliers
    }

    public function getSaleBalance(Sale $sale): float
    {
        return $this->transactionRepository->sumByTransactionable(Sale::class, $sale->id);
    }

    public function getPurchaseBalance(Purchase $purchase): float
    {
        return $this->transactionRepository->sumByTransactionable(Purchase::class, $purchase->id);
    }

    public function getCustomerLedger(int $customerId): array
    {
        $transactions = $this->transactionRepository->getCustomerLedger($customerId);
        $balance = $this->getCustomerBalance($customerId);
        
        return [
            'transactions' => $transactions,
            'balance' => $balance,
        ];
    }

    public function getSupplierLedger(int $supplierId): array
    {
        $transactions = $this->transactionRepository->getSupplierLedger($supplierId);
        $balance = $this->getSupplierBalance($supplierId);
        
        return [
            'transactions' => $transactions,
            'balance' => $balance,
        ];
    }

    public function getCustomerSummary(int $customerId): array
    {
        $totalSales = $this->transactionRepository->sumByCustomerAndType($customerId, Transaction::TYPE_SALE);
        $totalPayments = abs($this->transactionRepository->sumByCustomerAndType($customerId, Transaction::TYPE_PAYMENT_IN));
        $balance = $this->getCustomerBalance($customerId);
        
        return [
            'total_sales' => $totalSales,
            'total_payments' => $totalPayments,
            'balance' => $balance,
        ];
    }

    public function getSupplierSummary(int $supplierId): array
    {
        $totalPurchases = $this->transactionRepository->sumByCustomerAndType($supplierId, Transaction::TYPE_PURCHASE);
        $totalPayments = abs($this->transactionRepository->sumByCustomerAndType($supplierId, Transaction::TYPE_PAYMENT_OUT));
        $balance = $this->getSupplierBalance($supplierId);
        
        return [
            'total_purchases' => $totalPurchases,
            'total_payments' => $totalPayments,
            'balance' => $balance,
        ];
    }

    public function getTypes()
    {
        return array(
            Transaction::TYPE_SALE,
            Transaction::TYPE_PURCHASE,
            Transaction::TYPE_PAYMENT_IN,
            Transaction::TYPE_PAYMENT_OUT,
        );

    }

    private function calculateCustomerBalance(int $customerId): float
    {
        return $this->transactionRepository->sumByCustomer($customerId);
    }

    private function calculateSupplierBalance(int $supplierId): float
    {
        return $this->transactionRepository->sumByCustomer($supplierId); // Same method for suppliers
    }


}
