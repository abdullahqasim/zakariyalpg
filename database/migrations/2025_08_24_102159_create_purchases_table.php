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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // supplier
            $table->string('vehicle_number'); // e.g., TLJ-676
            $table->decimal('weight_ton', 8, 2); // e.g., 35.7 ton
            $table->decimal('rate_11_8_kg', 10, 2); // rate per 11.8kg cylinder
            $table->decimal('total_kg', 12, 2); // calculated: weight_ton * 1000
            $table->integer('total_cylinders'); // calculated: total_kg / 11.8
            $table->decimal('total_amount', 15, 2); // calculated: total_cylinders * rate_11_8_kg
            $table->string('reference_no')->unique(); // purchase reference number
            $table->enum('status', ['draft', 'confirmed', 'partially_paid', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('reference_no');
            $table->index('vehicle_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
