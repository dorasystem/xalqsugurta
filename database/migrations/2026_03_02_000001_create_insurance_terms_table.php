<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_terms', function (Blueprint $table) {
            $table->id();
            $table->string('product_code');                // osgop, osgor, kasko ...
            $table->unsignedInteger('provider_term_id');   // API ga yuboriladigan insuranceTermId
            $table->string('name_uz');
            $table->string('name_ru');
            $table->string('name_en');
            $table->unsignedTinyInteger('months');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_code');
            $table->unique(['product_code', 'provider_term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_terms');
    }
};
