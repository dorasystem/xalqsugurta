<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Answers to the site questionnaire (/{locale}/questionnaire), the old site's "Anketa" */
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->string('reason', 20);
            $table->string('reason_other', 300)->nullable();
            $table->json('products');
            $table->string('product_other', 300)->nullable();
            $table->json('ranking')->nullable();
            $table->string('locale', 2)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
