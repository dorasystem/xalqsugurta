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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_uz');
            $table->string('name_ru');
            $table->string('name_en');
            $table->text('desc_uz')->nullable();
            $table->text('desc_ru')->nullable();
            $table->text('desc_en')->nullable();
            $table->string('route');
            $table->string('icon')->default('bi bi-shield');
            $table->string('icon_color')->default('#2563eb');
            $table->string('icon_bg')->default('#dbeafe');
            $table->string('offerta_uz')->nullable();
            $table->string('offerta_ru')->nullable();
            $table->string('offerta_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
