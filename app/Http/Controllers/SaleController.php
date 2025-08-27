<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Sale;
use App\Models\User;
use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Services\PricingService;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private SaleService $saleService,
        private PricingService $pricingService
    ) {}

    /**
     * Display a listing of sales
     */
    public function index(Request $request): View
    {
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);
        
        if ($status) {
            $sales = $this->saleRepository->paginateByStatus($status, $perPage);
        } else {
            $sales = $this->saleRepository->paginate($perPage);
        }
        
        $statistics = $this->saleService->getSaleStatistics();
        
        return view('sales.index', compact('sales', 'statistics', 'status'));
    }

    /**
     * Show the form for creating a new sale
     */
    public function create(): View
    {
        $customers = User::where('type', 'customer')->get();
        $availableSizes = $this->pricingService->getAvailableSizes();
        
        return view('sales.create', compact('customers', 'availableSizes'));
    }

    /**
     * Store a newly created sale
     */
    public function store(SaleRequest $request): RedirectResponse
    {
        try {
            $sale = $this->saleService->createSale($request->validated());
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Sale created successfully. Invoice #' . $sale->invoice_no);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create sale: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sale
     */
    public function show(Sale $sale): View
    {
        $sale->load(['user', 'items', 'transactions.user']);
        
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified sale
     */
    public function edit(Sale $sale): View|RedirectResponse
    {
        if (!in_array($sale->status, [Sale::STATUS_DRAFT])) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', 'Only draft sales can be edited.');
        }
        
        $customers = User::where('type', 'customer')->get();
        $availableSizes = $this->pricingService->getAvailableSizes();
        $sale->load(['items']);
        
        return view('sales.edit', compact('sale', 'customers', 'availableSizes'));
    }

    /**
     * Update the specified sale
     */
    public function update(SaleRequest $request, Sale $sale): RedirectResponse
    {
        if (!in_array($sale->status, [Sale::STATUS_DRAFT])) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', 'Only draft sales can be edited.');
        }
        
        try {
            $sale = $this->saleService->updateSale($sale, $request->validated());
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update sale: ' . $e->getMessage());
        }
    }

    /**
     * Confirm a sale
     */
    public function confirm(Sale $sale): RedirectResponse
    {
        
        try {
            $this->saleService->confirmSale($sale);
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Sale confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a sale
     */
    public function cancel(Sale $sale): RedirectResponse
    {
        try {
            $this->saleService->cancelSale($sale);
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Sale cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display invoice for the specified sale
     */
    public function invoice(Sale $sale): View
    {
        $sale->load(['user', 'items', 'transactions.user']);
        
        return view('sales.invoice', compact('sale'));
    }

    /**
     * Calculate prices for cylinder sizes
     */
    public function calculatePrices(Request $request): \Illuminate\Http\JsonResponse
    {
        $basePrice = $request->get('base_price_11_8');
        
        if (!$basePrice || !is_numeric($basePrice)) {
            return response()->json(['error' => 'Invalid base price'], 400);
        }
        
        $prices = $this->pricingService->calculateAllPrices((float) $basePrice);
        
        return response()->json($prices);
    }

    /**
     * Get customer sales
     */
    public function customerSales(Request $request, int $userId): View
    {
        $perPage = $request->get('per_page', 15);
        $sales = $this->saleRepository->paginateByCustomer($userId, $perPage);
        $customer = User::findOrFail($userId);
        
        return view('sales.customer-sales', compact('sales', 'customer'));
    }
}
