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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Polymorphic relationship columns
            $table->morphs('transactionable'); // Creates transactionable_type and transactionable_id
            
            $table->enum('transaction_type', ['sale', 'purchase', 'payment_in', 'payment_out']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2)->default(0);
            $table->json('details')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'transaction_type']);
            $table->index('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
