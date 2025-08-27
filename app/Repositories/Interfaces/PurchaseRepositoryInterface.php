<?php

namespace App\Repositories\Interfaces;

use App\Models\Purchase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseRepositoryInterface
{
    public function create(array $data): Purchase;
    
    public function update(Purchase $purchase, array $data): Purchase;
    
    public function find(int $id): ?Purchase;
    
    public function findByReference(string $referenceNo): ?Purchase;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function getBySupplier(int $supplierId, int $perPage = 15): LengthAwarePaginator;
    
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator;
    
    public function getByVehicle(string $vehicleNumber): Collection;
    
    public function getSupplierPurchases(int $supplierId): Collection;
    
    public function getSupplierBalance(int $supplierId): float;
    
    public function getSupplierStatistics(int $supplierId): array;
    
    public function getAllSuppliers(): Collection;
    
    public function getUnpaidPurchases(): Collection;
    
    public function getTotalPurchases(): float;
    
    public function getTotalPaid(): float;
    
    public function getTotalOutstanding(): float;
}

