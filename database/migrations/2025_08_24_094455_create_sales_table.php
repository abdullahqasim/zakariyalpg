<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('base_price_11_8', 10, 2);
            $table->string('invoice_no')->unique();
            $table->enum('status', ['draft', 'confirmed', 'partially_paid', 'paid', 'cancelled'])->default('draft');
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
