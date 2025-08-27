<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LedgerController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display customer ledger index
     */
    public function index(Request $request): View
    {
        $customers = User::where('type', 'customer')->get();
        $selectedCustomer = null;
        $ledger = [];
        $summary = [];
        
        if ($request->has('customer_id')) {
            $customerId = $request->get('customer_id');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            $selectedCustomer = User::find($customerId);
            
            if ($selectedCustomer) {
                $ledger = $this->transactionService->getCustomerLedger($customerId, $startDate, $endDate);
                $summary = $this->transactionService->getCustomerSummary($customerId);
            }
        }

        // dd($customers, $selectedCustomer, $ledger, $summary);
        
        return view('ledger.index', compact('customers', 'selectedCustomer', 'ledger', 'summary'));
    }

    /**
     * Display ledger for a specific customer
     */
    public function customer(int $userId, Request $request): View
    {
        $customer = User::findOrFail($userId);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $ledger = $this->transactionService->getCustomerLedger($userId, $startDate, $endDate);
        $summary = $this->transactionService->getCustomerSummary($userId);
        
        return view('ledger.customer', compact('customer', 'ledger', 'summary', 'startDate', 'endDate'));
    }

    /**
     * Export customer ledger to PDF (requires DomPDF package)
     */
    public function exportPdf(int $userId, Request $request): \Illuminate\Http\Response
    {
        // This method requires the DomPDF package to be installed
        // For now, return a simple response
        return response('PDF export functionality requires DomPDF package installation', 501);
    }

    /**
     * Get customer balance summary
     */
    public function customerSummary(int $userId): \Illuminate\Http\JsonResponse
    {
        $customer = User::findOrFail($userId);
        $summary = $this->transactionService->getCustomerSummary($userId);
        
        return response()->json([
            'customer' => $customer,
            'summary' => $summary,
        ]);
    }
}
