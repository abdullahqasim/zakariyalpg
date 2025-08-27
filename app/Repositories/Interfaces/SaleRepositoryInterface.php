<?php

namespace App\Repositories\Interfaces;

use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;

interface SaleRepositoryInterface
{
    public function create(array $data): Sale;
    
    public function update(Sale $sale, array $data): Sale;
    
    public function find(int $id): ?Sale;
    
    public function findByInvoiceNo(string $invoiceNo): ?Sale;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function paginateByCustomer(int $userId, int $perPage = 15): LengthAwarePaginator;
    
    public function paginateByStatus(string $status, int $perPage = 15): LengthAwarePaginator;
    
    public function syncItems(Sale $sale, array $items): void;
    
    public function recalcTotals(Sale $sale): Sale;
    
    public function updateStatus(Sale $sale, string $status): Sale;
    
    public function getCustomerBalance(int $userId): float;
    
    public function getCustomerSales(int $userId): \Illuminate\Database\Eloquent\Collection;
}
