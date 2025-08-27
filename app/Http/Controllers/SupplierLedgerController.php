<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierLedgerController extends Controller
{
    public function __construct(
        private PurchaseRepositoryInterface $purchaseRepository,
        private PurchaseService $purchaseService
    ) {}

    public function index(Request $request): View
    {
        $supplierId = $request->get('supplier_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $suppliers = $this->purchaseRepository->getAllSuppliers();
        
        if ($supplierId) {
            $supplier = User::findOrFail($supplierId);
            $ledgerData = $this->purchaseService->getSupplierLedger($supplierId);
            $transactions = $ledgerData['transactions'];
            $statistics = $ledgerData['statistics'];
        } else {
            $supplier = null;
            $transactions = collect();
            $statistics = [
                'total_purchases' => 0,
                'total_paid' => 0,
                'balance' => 0,
                'purchase_count' => 0,
                'payment_count' => 0,
            ];
        }
        
        return view('supplier-ledger.index', compact(
            'suppliers',
            'supplier',
            'transactions',
            'statistics',
            'supplierId',
            'startDate',
            'endDate'
        ));
    }

    public function supplier(int $supplierId, Request $request): View
    {
        $supplier = User::findOrFail($supplierId);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $ledgerData = $this->purchaseService->getSupplierLedger($supplierId);
        $transactions = $ledgerData['transactions'];
        $statistics = $ledgerData['statistics'];
        
        return view('supplier-ledger.supplier', compact(
            'supplier',
            'transactions',
            'statistics',
            'startDate',
            'endDate'
        ));
    }

    public function supplierSummary(int $supplierId): View
    {
        $supplier = User::findOrFail($supplierId);
        $statistics = $this->purchaseRepository->getSupplierStatistics($supplierId);
        $purchases = $this->purchaseRepository->getSupplierPurchases($supplierId);
        
        return view('supplier-ledger.summary', compact('supplier', 'statistics', 'purchases'));
    }
}
