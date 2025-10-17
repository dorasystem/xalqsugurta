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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name_ru'); // Ruscha nomi
            $table->string('name_uz'); // O‘zbekcha nomi
            $table->string('name_en'); // Inglizcha nomi
            $table->string('code')->unique(); // Kod (ISO Code)
            $table->text('type')->nullable(); // Kod (ISO Code)
            $table->boolean('active')->default(1); // Aktiv yoki yo‘qligi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
