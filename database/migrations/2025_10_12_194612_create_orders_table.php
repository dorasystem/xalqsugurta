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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->integer('state')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('insurance_id');
            $table->string('phone')->nullable();
            $table->text('insurances_data')->nullable();
            $table->text('insurances_response_data')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
