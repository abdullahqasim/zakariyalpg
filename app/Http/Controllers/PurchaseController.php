<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use App\Models\User;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PurchaseController extends Controller
{
    public function __construct(
        private PurchaseRepositoryInterface $purchaseRepository,
        private PurchaseService $purchaseService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->get('status');
        $supplierId = $request->get('supplier_id');
        $perPage = $request->get('per_page', 15); // default 15

        if ($status) {
            $purchases = $this->purchaseRepository
                ->getByStatus($status)
                ->paginate($perPage)
                ->appends($request->query());
        } elseif ($supplierId) {
            $purchases = $this->purchaseRepository
                ->getBySupplier($supplierId)
                ->paginate($perPage)
                ->appends($request->query());
        } else {
            $purchases = $this->purchaseRepository
                ->paginate($perPage)
                ->appends($request->query());
        }
        
        $statistics = $this->purchaseService->getPurchaseStatistics();
        $suppliers = $this->purchaseRepository->getAllSuppliers();
        // dd($purchases, $statistics, $suppliers, $status, $supplierId);
        return view('purchases.index', compact('purchases', 'statistics', 'suppliers', 'status', 'supplierId'));
    }

    public function create(): View
    {
        $suppliers = $this->purchaseRepository->getAllSuppliers();
        return view('purchases.create', compact('suppliers'));
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        try {
            $purchase = $this->purchaseService->createPurchase($request->validated());
            
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Purchase created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create purchase: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load('supplier', 'transactions');
        $statistics = $this->purchaseRepository->getSupplierStatistics($purchase->user_id);
        
        return view('purchases.show', compact('purchase', 'statistics'));
    }

    public function edit(Purchase $purchase): View|RedirectResponse
    {
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be edited.');
        }
        
        $suppliers = $this->purchaseRepository->getAllSuppliers();
        return view('purchases.edit', compact('purchase', 'suppliers'));
    }

    public function update(PurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        if ($purchase->status !== Purchase::STATUS_DRAFT) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be updated.');
        }
        
        try {
            $purchase = $this->purchaseService->updatePurchase($purchase, $request->validated());
            
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update purchase: ' . $e->getMessage());
        }
    }

    public function confirm(Purchase $purchase): RedirectResponse
    {
        try {
            $purchase = $this->purchaseService->confirmPurchase($purchase);
            
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Purchase confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to confirm purchase: ' . $e->getMessage());
        }
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        try {
            $purchase = $this->purchaseService->cancelPurchase($purchase);
            
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Purchase cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel purchase: ' . $e->getMessage());
        }
    }

    public function createPayment(Purchase $purchase): View|RedirectResponse
    {
        if ($purchase->is_paid) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('error', 'This purchase is already fully paid.');
        }
        
        return view('purchases.create-payment', compact('purchase'));
    }

    public function recordPayment(Request $request, Purchase $purchase): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $purchase->balance],
            'details' => ['nullable', 'string', 'max:500'],
        ]);
        
        try {
            $transaction = $this->purchaseService->createRecordPayment(
                $purchase,
                (float) $request->amount,
                $request->details
            );
            
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function payRemaining(Purchase $purchase): RedirectResponse
    {
        if ($purchase->is_paid) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('error', 'This purchase is already fully paid.');
        }
        
        try {
            $transaction = $this->purchaseService->createPaymentOut(
                $purchase,
                $purchase->balance,
                'Full payment for remaining balance'
            );
            
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Full payment recorded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function supplierPurchases(int $supplierId): View
    {
        $supplier = User::findOrFail($supplierId);
        $purchases = $this->purchaseRepository->getSupplierPurchases($supplierId);
        $statistics = $this->purchaseRepository->getSupplierStatistics($supplierId);
        
        return view('purchases.supplier', compact('supplier', 'purchases', 'statistics'));
    }

    public function calculateTotals(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'weight_ton' => ['required', 'numeric', 'min:0.01'],
            'rate_11_8_kg' => ['required', 'numeric', 'min:0.01'],
        ]);
        
        $weightTon = (float) $request->weight_ton;
        $rate11_8Kg = (float) $request->rate_11_8_kg;
        
        $totalKg = $weightTon * 1000;
        $totalCylinders = (int) round($totalKg / 11.8);
        $totalAmount = $totalCylinders * $rate11_8Kg;
        
        return response()->json([
            'total_kg' => number_format($totalKg, 0),
            'total_cylinders' => number_format($totalCylinders, 0),
            'total_amount' => number_format($totalAmount, 2),
            'formatted_total_amount' => 'PKR' . number_format($totalAmount, 2),
        ]);
    }
}
