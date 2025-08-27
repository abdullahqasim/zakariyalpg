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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['cash', 'bank_deposit'])->nullable();
            $table->string('name'); // e.g., JazzCash, Easypaisa, HBL, UBL
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
    
        // Drop payment_methods table
        Schema::dropIfExists('payment_methods');
    }
};
