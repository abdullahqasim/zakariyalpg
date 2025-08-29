<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface
{
    public function create(array $data): Transaction;
    
    public function update(Transaction $transaction, array $data): Transaction;
    
    public function find(int $id): ?Transaction;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function findByTransactionable(string $type, int $id): Collection;
    
    public function findByCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator;
    
    public function sumByTransactionable(string $type, int $id): float;
    
    public function sumByCustomer(int $customerId): float;
    
    public function sumByCustomerAndType(int $customerId, string $type): float;
    
    public function getCustomerLedger(int $customerId): Collection;
    
    public function findByReference(string $referenceNo): ?Transaction;
    
    public function getUserLedger(int $userId, $startDate, $endDate): Collection;

    public function getOpeningBalance(int $userId, $startDate): float;
    
    public function getSupplierLedger(int $supplierId): Collection;
    
    public function getTransactionsByType(string $type, int $perPage = 15): LengthAwarePaginator;
    
    public function getTransactionsByUser(int $userId, int $perPage = 15): LengthAwarePaginator;
}
