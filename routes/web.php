<?php

use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierLedgerController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});
Auth::routes();
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    // Sales Routes
    Route::resource('sales', SaleController::class);
    Route::get('sales/{sale}/confirm', [SaleController::class, 'confirm'])->name('sales.confirm');
    Route::get('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
    Route::get('sales/customer/{userId}', [SaleController::class, 'customerSales'])->name('sales.customer');
    Route::post('sales/calculate-prices', [SaleController::class, 'calculatePrices'])->name('sales.calculate-prices');
    
    // Purchase Routes
    Route::resource('purchases', PurchaseController::class);
    Route::get('purchases/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('purchases.confirm');
    Route::get('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::get('purchases/{purchase}/payment', [PurchaseController::class, 'createPayment'])->name('purchases.create-payment');
    Route::post('purchases/{purchase}/payment', [PurchaseController::class, 'storePayment'])->name('purchases.store-payment');
    Route::get('purchases/{purchase}/pay-remaining', [PurchaseController::class, 'payRemaining'])->name('purchases.pay-remaining');
    Route::get('purchases/supplier/{supplierId}', [PurchaseController::class, 'supplierPurchases'])->name('purchases.supplier');
    Route::post('purchases/calculate-totals', [PurchaseController::class, 'calculateTotals'])->name('purchases.calculate-totals');
    
    // Transaction Routes
    Route::resource('transactions', TransactionController::class)->except(['edit', 'update', 'destroy']);
    Route::get('sales/{sale}/payment', [TransactionController::class, 'createPayment'])->name('transactions.create-payment');
    Route::post('sales/{sale}/payment', [TransactionController::class, 'storePayment'])->name('transactions.store-payment');
    Route::get('sales/{sale}/pay-remaining', [TransactionController::class, 'payRemaining'])->name('transactions.pay-remaining');
    Route::get('sales/{sale}/refund', [TransactionController::class, 'createRefund'])->name('transactions.create-refund');
    Route::post('sales/{sale}/refund', [TransactionController::class, 'storeRefund'])->name('transactions.store-refund');
    Route::get('sales/{sale}/adjustment', [TransactionController::class, 'createAdjustment'])->name('transactions.create-adjustment');
    Route::post('sales/{sale}/adjustment', [TransactionController::class, 'storeAdjustment'])->name('transactions.store-adjustment');
    Route::get('transactions/customer/{userId}/balance', [TransactionController::class, 'customerBalance'])->name('transactions.customer-balance');
    
    // Customer Ledger Routes
    Route::get('ledger', [LedgerController::class, 'index'])->name('ledger.index');
    Route::get('ledger/customer/{userId}', [LedgerController::class, 'customer'])->name('ledger.customer');
    Route::get('ledger/customer/{userId}/export', [LedgerController::class, 'exportPdf'])->name('ledger.export-pdf');
    Route::get('ledger/customer/{userId}/summary', [LedgerController::class, 'customerSummary'])->name('ledger.customer-summary');
    
    // Supplier Ledger Routes
    Route::get('supplier-ledger', [SupplierLedgerController::class, 'index'])->name('supplier-ledger.index');
    Route::get('supplier-ledger/supplier/{supplierId}', [SupplierLedgerController::class, 'supplier'])->name('supplier-ledger.supplier');
    Route::get('supplier-ledger/supplier/{supplierId}/summary', [SupplierLedgerController::class, 'supplierSummary'])->name('supplier-ledger.summary');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
