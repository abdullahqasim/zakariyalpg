<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Models\User;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }
    
    public function update(Purchase $purchase, array $data): Purchase
    {
        $purchase->update($data);
        return $purchase->fresh();
    }
    
    public function find(int $id): ?Purchase
    {
        return Purchase::find($id);
    }
    
    public function findByReference(string $referenceNo): ?Purchase
    {
        return Purchase::where('reference_no', $referenceNo)->first();
    }
    
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function getBySupplier(int $supplierId, int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::with('supplier')
            ->where('user_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::with('supplier')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function getByVehicle(string $vehicleNumber): Collection
    {
        return Purchase::with('supplier')
            ->where('vehicle_number', $vehicleNumber)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getSupplierPurchases(int $supplierId): Collection
    {
        return Purchase::where('user_id', $supplierId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getSupplierBalance(int $supplierId): float
    {
        $totalPurchases = Purchase::where('user_id', $supplierId)
            ->sum('total_amount');
        
        $totalPayments = \App\Models\Transaction::where('user_id', $supplierId)
            ->where('transaction_type', \App\Models\Transaction::TYPE_PAYMENT_OUT)
            ->sum('amount');
        
        return $totalPurchases - abs($totalPayments);
    }
    
    public function getSupplierStatistics(int $supplierId): array
    {
        $purchases = Purchase::where('user_id', $supplierId);
        $totalPurchases = $purchases->sum('total_amount');
        
        $payments = \App\Models\Transaction::where('user_id', $supplierId)
            ->where('transaction_type', \App\Models\Transaction::TYPE_PAYMENT_OUT);
        $totalPaid = abs($payments->sum('amount'));
        
        return [
            'total_purchases' => $totalPurchases,
            'total_paid' => $totalPaid,
            'balance' => $totalPurchases - $totalPaid,
            'purchase_count' => $purchases->count(),
            'payment_count' => $payments->count(),
        ];
    }
    
    public function getAllSuppliers(): Collection
    {
        return User::suppliers()->orderBy('name')->get();
    }
    
    public function getUnpaidPurchases(): Collection
    {
        return Purchase::with('supplier')
            ->whereIn('status', [Purchase::STATUS_CONFIRMED, Purchase::STATUS_PARTIALLY_PAID])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getTotalPurchases(): float
    {
        return Purchase::sum('total_amount');
    }
    
    public function getTotalPaid(): float
    {
        return abs(\App\Models\Transaction::where('transaction_type', \App\Models\Transaction::TYPE_PAYMENT_OUT)
            ->sum('amount'));
    }
    
    public function getTotalOutstanding(): float
    {
        return $this->getTotalPurchases() - $this->getTotalPaid();
    }
}

