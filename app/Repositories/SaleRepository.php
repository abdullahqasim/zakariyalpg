<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SaleRepository implements SaleRepositoryInterface
{
    public function create(array $data): Sale
    {
        return Sale::create($data);
    }
    
    public function update(Sale $sale, array $data): Sale
    {
        $sale->update($data);
        return $sale->fresh();
    }
    
    public function find(int $id): ?Sale
    {
        return Sale::with(['user', 'items', 'transactions'])->find($id);
    }
    
    public function findByInvoiceNo(string $invoiceNo): ?Sale
    {
        return Sale::with(['user', 'items', 'transactions'])
            ->where('invoice_no', $invoiceNo)
            ->first();
    }
    
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Sale::with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function paginateByCustomer(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Sale::with(['user', 'items'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function paginateByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Sale::with(['user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function syncItems(Sale $sale, array $items): void
    {
        // Delete existing items
        $sale->items()->delete();
        
        // Create new items
        foreach ($items as $item) {
            if (!empty($item['quantity']) && $item['quantity'] > 0) {
                $sale->items()->create([
                    'size_kg' => $item['size_kg'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        }
    }
    
    public function recalcTotals(Sale $sale): Sale
    {
        $subTotal = $sale->items()->sum('line_total');
        $grandTotal = $subTotal - $sale->discount_amount;
        
        $sale->update([
            'sub_total' => $subTotal,
            'grand_total' => $grandTotal,
        ]);
        
        return $sale->fresh();
    }
    
    public function updateStatus(Sale $sale, string $status): Sale
    {
        $sale->update(['status' => $status]);
        return $sale->fresh();
    }
    
    public function getCustomerBalance(int $userId): float
    {
        $totalBilled = Sale::where('user_id', $userId)
            ->whereNotIn('status', [Sale::STATUS_DRAFT, Sale::STATUS_CANCELLED])
            ->sum('grand_total');
            
        $totalPaid = \App\Models\Transaction::where('user_id', $userId)
            ->whereIn('type', ['payment', 'refund', 'adjustment'])
            ->sum('amount');
            
        return $totalBilled + $totalPaid; // +ve = owes, -ve = overpaid
    }
    
    public function getCustomerSales(int $userId): Collection
    {
        return Sale::with(['items', 'transactions'])
            ->where('user_id', $userId)
            ->whereNotIn('status', [Sale::STATUS_DRAFT, Sale::STATUS_CANCELLED])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
