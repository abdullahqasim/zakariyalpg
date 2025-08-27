<?php

namespace App\Providers;

use App\Repositories\Interfaces\SaleRepositoryInterface;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use App\Repositories\Interfaces\PurchaseRepositoryInterface;
use App\Repositories\SaleRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\PurchaseRepository;
use App\Services\InvoiceNumberService;
use App\Services\PurchaseService;
use App\Services\PricingService;
use App\Services\SaleService;
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class GasSalesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(PurchaseRepositoryInterface::class, PurchaseRepository::class);
        
        // Register services
        $this->app->singleton(PricingService::class);
        $this->app->singleton(InvoiceNumberService::class);
        $this->app->singleton(SaleService::class);
        $this->app->singleton(TransactionService::class);
        $this->app->singleton(PurchaseService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
