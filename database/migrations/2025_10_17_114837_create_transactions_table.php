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
            $table->string('transaction')->nullable();
            $table->string('code')->nullable();
            $table->string('state')->nullable();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->string('reason')->nullable();
            $table->string('payme_time')->nullable();
            $table->string('cancel_time')->nullable();
            $table->string('create_time')->nullable();
            $table->string('perform_time')->nullable();
            $table->string('paycom_transaction_id')->nullable(); // Qo'shildi
            $table->string('paycom_time')->nullable(); // Qo'shildi
            $table->timestamps();
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
