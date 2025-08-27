<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\PricingService;
use App\Services\InvoiceNumberService;

class GasSalesTestSeeder extends Seeder
{
    public function run(): void
    {
        // Create products (gas cylinder sizes)
        $products = [
            ['name' => '6kg Cylinder', 'size_kg' => 6.0, 'description' => 'Small cylinder for domestic use'],
            ['name' => '11.8kg Cylinder', 'size_kg' => 11.8, 'description' => 'Standard cylinder size'],
            ['name' => '15kg Cylinder', 'size_kg' => 15.0, 'description' => 'Medium cylinder size'],
            ['name' => '45.6kg Cylinder', 'size_kg' => 45.6, 'description' => 'Large cylinder for commercial use'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Get customers and suppliers
        $customers = User::customers()->get();
        $suppliers = User::suppliers()->get();

        if ($customers->isEmpty() || $suppliers->isEmpty()) {
            $this->command->warn('No customers or suppliers found. Please run DatabaseSeeder first.');
            return;
        }

        $pricingService = new PricingService();
        $invoiceService = new InvoiceNumberService();

        // Create test sales
        foreach ($customers as $customer) {
            // Create 2-3 sales per customer
            for ($i = 0; $i < rand(2, 3); $i++) {
                $basePrice = rand(2000, 3000); // Random base price between 2000-3000
                
                // Create sale
                $sale = Sale::create([
                    'user_id' => $customer->id,
                    'base_price_11_8' => $basePrice,
                    'invoice_no' => $invoiceService->generateInvoiceNumber(),
                    'status' => Sale::STATUS_CONFIRMED,
                    'sub_total' => 0, // Will be calculated
                    'discount_amount' => rand(0, 500), // Random discount
                    'grand_total' => 0, // Will be calculated
                ]);

                // Create sale items
                $subTotal = 0;
                $availableSizes = [6.0, 11.8, 15.0, 45.6];
                
                foreach ($availableSizes as $size) {
                    $quantity = rand(0, 5); // Random quantity 0-5
                    if ($quantity > 0) {
                        $unitPrice = $pricingService->calculateProportionalPrice($basePrice, $size);
                        $lineTotal = $pricingService->calculateLineTotal($unitPrice, $quantity);
                        
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'size_kg' => $size,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'line_total' => $lineTotal,
                        ]);
                        
                        $subTotal += $lineTotal;
                    }
                }

                // Update sale totals
                $grandTotal = $subTotal - $sale->discount_amount;
                $sale->update([
                    'sub_total' => $subTotal,
                    'grand_total' => $grandTotal,
                ]);

                // Create sale transaction
                Transaction::create([
                    'user_id' => $customer->id,
                    'transactionable_type' => Sale::class,
                    'transactionable_id' => $sale->id,
                    'transaction_type' => Transaction::TYPE_SALE,
                    'amount' => $grandTotal,
                    'balance' => $grandTotal, // Initial balance
                    'details' => [
                        'invoice_no' => $sale->invoice_no,
                        'base_price_11_8' => $sale->base_price_11_8,
                        'sub_total' => $sale->sub_total,
                        'discount_amount' => $sale->discount_amount,
                        'grand_total' => $sale->grand_total,
                    ],
                ]);

                // Create some payment transactions for confirmed sales
                if ($sale->status === Sale::STATUS_CONFIRMED) {
                    $paymentAmount = rand(0, $grandTotal); // Random payment amount
                    if ($paymentAmount > 0) {
                        Transaction::create([
                            'user_id' => $customer->id,
                            'transactionable_type' => Sale::class,
                            'transactionable_id' => $sale->id,
                            'transaction_type' => Transaction::TYPE_PAYMENT_IN,
                            'amount' => -$paymentAmount, // Negative for payment
                            'balance' => $grandTotal - $paymentAmount,
                            'details' => [
                                'payment_details' => 'Test payment',
                                'invoice_no' => $sale->invoice_no,
                            ],
                        ]);

                        // Update sale status based on payment
                        if ($paymentAmount >= $grandTotal) {
                            $sale->update(['status' => Sale::STATUS_PAID]);
                        } elseif ($paymentAmount > 0) {
                            $sale->update(['status' => Sale::STATUS_PARTIALLY_PAID]);
                        }
                    }
                }
            }
        }

        // Create test purchases
        $purchaseCounter = 1;
        foreach ($suppliers as $supplier) {
            // Create 1-2 purchases per supplier
            for ($i = 0; $i < rand(1, 2); $i++) {
                $weightTon = rand(10, 50); // Random weight between 10-50 tons
                $rate11_8 = rand(2000, 3000); // Random rate between 2000-3000
                
                // Calculate totals
                $totalKg = $weightTon * 1000;
                $totalCylinders = $totalKg / 11.8;
                $totalAmount = $totalCylinders * $rate11_8;

                // Create purchase
                $purchase = Purchase::create([
                    'user_id' => $supplier->id,
                    'vehicle_number' => 'TLJ-' . rand(100, 999),
                    'weight_ton' => $weightTon,
                    'rate_11_8_kg' => $rate11_8,
                    'total_kg' => $totalKg,
                    'total_cylinders' => $totalCylinders,
                    'total_amount' => $totalAmount,
                    'reference_no' => 'PUR-' . date('Ym') . '-' . str_pad($purchaseCounter++, 4, '0', STR_PAD_LEFT),
                    'status' => Purchase::STATUS_CONFIRMED,
                    'notes' => 'Test purchase order',
                ]);

                // Create purchase transaction
                Transaction::create([
                    'user_id' => $supplier->id,
                    'transactionable_type' => Purchase::class,
                    'transactionable_id' => $purchase->id,
                    'transaction_type' => Transaction::TYPE_PURCHASE,
                    'amount' => $totalAmount,
                    'balance' => $totalAmount, // Initial balance
                    'details' => [
                        'vehicle_number' => $purchase->vehicle_number,
                        'weight_ton' => $purchase->weight_ton,
                        'total_kg' => $purchase->total_kg,
                        'total_cylinders' => $purchase->total_cylinders,
                        'rate_11_8_kg' => $purchase->rate_11_8_kg,
                        'reference_no' => $purchase->reference_no,
                    ],
                ]);

                // Create some payment transactions for confirmed purchases
                if ($purchase->status === Purchase::STATUS_CONFIRMED) {
                    $paymentAmount = rand(0, $totalAmount); // Random payment amount
                    if ($paymentAmount > 0) {
                        Transaction::create([
                            'user_id' => $supplier->id,
                            'transactionable_type' => Purchase::class,
                            'transactionable_id' => $purchase->id,
                            'transaction_type' => Transaction::TYPE_PAYMENT_OUT,
                            'amount' => -$paymentAmount, // Negative for payment
                            'balance' => $totalAmount - $paymentAmount,
                            'details' => [
                                'payment_details' => 'Test payment to supplier',
                                'reference_no' => $purchase->reference_no,
                            ],
                        ]);

                        // Update purchase status based on payment
                        if ($paymentAmount >= $totalAmount) {
                            $purchase->update(['status' => Purchase::STATUS_PAID]);
                        } elseif ($paymentAmount > 0) {
                            $purchase->update(['status' => Purchase::STATUS_PARTIALLY_PAID]);
                        }
                    }
                }
            }
        }

        $this->command->info('Gas Sales test data seeded successfully!');
        $this->command->info('Admin login: admin@example.com / password');
        $this->command->info('Customer login: john@example.com / password');
        $this->command->info('Supplier login: supplier1@example.com / password');
    }
}
