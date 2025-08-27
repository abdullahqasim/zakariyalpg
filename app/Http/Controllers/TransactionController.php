<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display a listing of transactions
     */
    public function index(Request $request): View
    {
        // dd(Transaction::find(1)->transactionable);
        $type = $request->get('type');
        $perPage = $request->get('per_page', 15);
        
        $query = Transaction::with(['transactionable', 'user']);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);
        // dd($transactions);
        
        return view('transactions.index', compact('transactions', 'type'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function createPayment(Sale $sale): View
    {
        $sale->load(['user']);
        $paymentMethods = PaymentMethod::where('is_active', 1)->get();
        
        return view('transactions.create-payment', compact('sale', 'paymentMethods'));
    }

    /**
     * Store a new payment
     */
    // public function storePayment(TransactionRequest $request, Sale $sale): RedirectResponse
    public function storePayment(Request $request, Sale $sale): RedirectResponse
    {
        $data = $request->all();
        try {
            // $data = $request->validated();
            $data['sale_id'] = $sale->id;
            $data['type'] = Transaction::TYPE_PAYMENT_IN;
            
            $transaction = $this->transactionService->createPaymentIn($sale, $data);
            
            // $totalBalance = $sale->total_balance;

            // if($totalBalance <= 0){
            //     $sale->update(['status'=> Sale::STATUS_PAID ]);
            // }
            // if($totalBalance >= 0 && $totalBalance <= $sale->grand_tatal){
            //     $sale->update(['status'=> Sale::STATUS_PARTIALLY_PAID ]);
            // }
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Pay remaining balance for a sale
     */
    public function payRemaining(Sale $sale, Request $request): RedirectResponse
    {
        try {
            $method = $request->get('method', 'cash');
            $reference = $request->get('reference');
            
            $transaction = $this->transactionService->payRemainingBalance($sale, $method, $reference);
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Remaining balance paid successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a refund
     */
    public function createRefund(Sale $sale): View
    {
        $sale->load(['user']);
        
        return view('transactions.create-refund', compact('sale'));
    }

    /**
     * Store a new refund
     */
    public function storeRefund(TransactionRequest $request, Sale $sale): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['sale_id'] = $sale->id;
            $data['type'] = Transaction::TYPE_REFUND;
            
            $transaction = $this->transactionService->createRefund($data);
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Refund recorded successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record refund: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating an adjustment
     */
    public function createAdjustment(Sale $sale): View
    {
        $sale->load(['user']);
        
        return view('transactions.create-adjustment', compact('sale'));
    }

    /**
     * Store a new adjustment
     */
    public function storeAdjustment(TransactionRequest $request, Sale $sale): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['sale_id'] = $sale->id;
            $data['type'] = Transaction::TYPE_ADJUSTMENT;
            
            $transaction = $this->transactionService->createAdjustment($data);
            
            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Adjustment recorded successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a standalone transaction
     */
    public function create(Request $request): View
    {
        $customers = User::where('role', 'customer')->get();
        $sales = Sale::whereNotIn('status', [Sale::STATUS_DRAFT, Sale::STATUS_CANCELLED])->get();
        $transactionTypes = Transaction::getTypes();
        $paymentMethods = Transaction::getPaymentMethods();
        
        $selectedCustomer = $request->get('customer_id');
        $selectedSale = $request->get('sale_id');
        
        return view('transactions.create', compact(
            'customers', 
            'sales', 
            'transactionTypes', 
            'paymentMethods',
            'selectedCustomer',
            'selectedSale'
        ));
    }

    /**
     * Store a new standalone transaction
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            
            $transaction = match ($data['type']) {
                Transaction::TYPE_PAYMENT => $this->transactionService->createPayment($data),
                Transaction::TYPE_REFUND => $this->transactionService->createRefund($data),
                Transaction::TYPE_ADJUSTMENT => $this->transactionService->createAdjustment($data),
                default => throw new \Exception('Invalid transaction type'),
            };
            
            $redirectRoute = $data['sale_id'] 
                ? route('sales.show', $data['sale_id'])
                : route('transactions.index');
            
            return redirect($redirectRoute)
                ->with('success', ucfirst($data['type']) . ' recorded successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record transaction: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['sale', 'user']);
        
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Get customer balance
     */
    public function customerBalance(int $userId): \Illuminate\Http\JsonResponse
    {
        $balance = $this->transactionService->getCustomerBalance($userId);
        $customer = User::findOrFail($userId);
        
        return response()->json([
            'balance' => $balance,
            'formatted_balance' => 'PKR' . number_format($balance, 2),
            'customer_name' => $customer->name,
        ]);
    }
}
