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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->integer('year');
            $table->string('vin', 17)->unique();
            $table->string('license_plate')->unique();
            $table->string('color');
            $table->string('engine_type');
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid']);
            $table->enum('transmission', ['manual', 'automatic', 'cvt']);
            $table->integer('mileage')->default(0);
            $table->enum('status', ['active', 'inactive', 'sold'])->default('active');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->date('insurance_expires_at')->nullable();
            $table->timestamps();

            $table->index(['brand', 'model']);
            $table->index('year');
            $table->index('status');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
