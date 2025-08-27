<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Transaction;

class SaleService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private TransactionRepositoryInterface $transactionRepository,
        private PricingService $pricingService,
        private InvoiceNumberService $invoiceNumberService
    ) {}
    
    /**
     * Create a new sale with items and initial transaction
     */
    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Generate invoice number
            $data['invoice_no'] = $this->invoiceNumberService->generateInvoiceNumber();
            
            // Calculate prices for items
            $items = $this->prepareSaleItems($data['base_price_11_8'], $data['items'] ?? []);
            
            // Calculate totals
            $totals = $this->pricingService->calculateSaleTotals($items, $data['discount_amount'] ?? 0);
            
            // Create sale
            $sale = $this->saleRepository->create(array_merge($data, $totals));
            
            // Sync items
            $this->saleRepository->syncItems($sale, $items);
            
            // Create initial sale transaction
            $this->createSaleTransaction($sale);
            
            return $sale->load(['user', 'items', 'transactions']);
        });
    }
    
    /**
     * Update an existing sale
     */
    public function updateSale(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            // Calculate prices for items if base price changed
            if (isset($data['base_price_11_8'])) {
                $items = $this->prepareSaleItems($data['base_price_11_8'], $data['items'] ?? []);
                $data['items'] = $items;
            }
            
            // Calculate totals if items or discount changed
            if (isset($data['items']) || isset($data['discount_amount'])) {
                $items = $data['items'] ?? $sale->items->toArray();
                $discountAmount = $data['discount_amount'] ?? $sale->discount_amount;
                $totals = $this->pricingService->calculateSaleTotals($items, $discountAmount);
                $data = array_merge($data, $totals);
            }
            
            // Update sale
            $sale = $this->saleRepository->update($sale, $data);
            
            // Sync items if provided
            if (isset($data['items'])) {
                $this->saleRepository->syncItems($sale, $data['items']);
            }
            
            // Update sale transaction amount
            $saleTransaction = $sale->transactions()->where('type', 'sale')->first();
            if ($saleTransaction) {
                $this->transactionRepository->update($saleTransaction, [
                    'amount' => $sale->grand_total,
                ]);
            }
            
            return $sale->load(['user', 'items', 'transactions']);
        });
    }
    
    /**
     * Confirm a sale (change status from draft to confirmed)
     */
    public function confirmSale(Sale $sale): Sale
    {
        if ($sale->status !== Sale::STATUS_DRAFT) {
            throw new Exception('Only draft sales can be confirmed');
        }
        
        return $this->saleRepository->updateStatus($sale, Sale::STATUS_CONFIRMED);
    }
    
    /**
     * Cancel a sale
     */
    public function cancelSale(Sale $sale): Sale
    {
        if (!in_array($sale->status, [Sale::STATUS_DRAFT, Sale::STATUS_CONFIRMED])) {
            throw new Exception('Only draft or confirmed sales can be cancelled');
        }
        
        return $this->saleRepository->updateStatus($sale, Sale::STATUS_CANCELLED);
    }
    
    /**
     * Update sale status based on payment balance
     */
    public function updateSaleStatus(Sale $sale): Sale
    {
        $balance = $sale->balance;
        $grandTotal = $sale->grand_total;
        
        $newStatus = match (true) {
            $balance <= 0 => Sale::STATUS_PAID,
            $balance < $grandTotal => Sale::STATUS_PARTIALLY_PAID,
            $balance == $grandTotal => Sale::STATUS_CONFIRMED,
            default => $sale->status,
        };
        
        if ($newStatus !== $sale->status) {
            return $this->saleRepository->updateStatus($sale, $newStatus);
        }
        
        return $sale;
    }
    
    /**
     * Prepare sale items with calculated prices
     */
    private function prepareSaleItems(float $basePrice11_8, array $items): array
    {
        $preparedItems = [];
        
        foreach ($items as $item) {
            if (!empty($item['quantity']) && $item['quantity'] > 0) {
                $sizeKg = $item['size_kg'];
                
                // Use provided unit price or calculate proportional price
                $unitPrice = $item['unit_price'] ?? $this->pricingService->calculateProportionalPrice($basePrice11_8, $sizeKg);
                
                $preparedItems[] = [
                    'size_kg' => $sizeKg,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $this->pricingService->calculateLineTotal($unitPrice, $item['quantity']),
                ];
            }
        }
        
        return $preparedItems;
    }
    
    /**
     * Get sale summary statistics
     */
    public function getSaleStatistics(): array
    {
        $totalSales = Sale::whereNotIn('status', [Sale::STATUS_DRAFT, Sale::STATUS_CANCELLED])->count();
        $totalRevenue = Sale::whereNotIn('status', [Sale::STATUS_DRAFT, Sale::STATUS_CANCELLED])->sum('grand_total');
        $pendingAmount = Sale::whereIn('status', [Sale::STATUS_CONFIRMED, Sale::STATUS_PARTIALLY_PAID])->sum('grand_total');
        
        return [
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'pending_amount' => $pendingAmount,
        ];
    }

    private function createSaleTransaction(Sale $sale): Transaction
    {
        return $this->transactionRepository->create([
            'user_id' => $sale->user_id,
            'transactionable_type' => Sale::class,
            'transactionable_id' => $sale->id,
            'transaction_type' => Transaction::TYPE_SALE,
            'amount' => $sale->grand_total,
            'balance' => $this->calculateCustomerBalance($sale->user_id),
            'details' => [
                'invoice_no' => $sale->invoice_no,
                'base_price_11_8' => $sale->base_price_11_8,
                'sub_total' => $sale->sub_total,
                'discount_amount' => $sale->discount_amount,
                'grand_total' => $sale->grand_total,
            ],
        ]);
    }

    private function calculateCustomerBalance(int $customerId): float
    {
        return $this->transactionRepository->sumByCustomer($customerId);
    }
}
