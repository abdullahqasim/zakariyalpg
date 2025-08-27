<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }
    
    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        return $transaction->fresh();
    }
    
    public function find(int $id): ?Transaction
    {
        return Transaction::find($id);
    }
    
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['user', 'transactionable'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function findByTransactionable(string $type, int $id): Collection
    {
        return Transaction::with(['user', 'transactionable'])
            ->where('transactionable_type', $type)
            ->where('transactionable_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function findByCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['user', 'transactionable'])
            ->where('user_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function sumByTransactionable(string $type, int $id): float
    {
        return Transaction::where('transactionable_type', $type)
            ->where('transactionable_id', $id)
            ->sum('amount');
    }
    
    public function sumByCustomer(int $customerId): float
    {
        return Transaction::where('user_id', $customerId)
            ->sum('amount');
    }
    
    public function sumByCustomerAndType(int $customerId, string $type): float
    {
        return Transaction::where('user_id', $customerId)
            ->where('transaction_type', $type)
            ->sum('amount');
    }
    
    public function getCustomerLedger(int $customerId): Collection
    {
        return Transaction::with(['user', 'transactionable'])
            ->where('user_id', $customerId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    public function findByReference(string $referenceNo): ?Transaction
    {
        // For polymorphic relationships, we need to search through the transactionable models
        // This is a simplified approach - in practice, you might want to store reference in details
        return Transaction::whereHasMorph('transactionable', [
            \App\Models\Sale::class,
            \App\Models\Purchase::class
        ], function ($query) use ($referenceNo) {
            $query->where('invoice_no', $referenceNo)
                  ->orWhere('reference_no', $referenceNo);
        })->first();
    }
    
    public function getUserLedger(int $userId): Collection
    {
        return Transaction::with(['user', 'transactionable'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    public function getSupplierLedger(int $supplierId): Collection
    {
        return Transaction::with(['user', 'transactionable'])
            ->where('user_id', $supplierId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    public function getTransactionsByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['user', 'transactionable'])
            ->where('transaction_type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    public function getTransactionsByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::with(['user', 'transactionable'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
