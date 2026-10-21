<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Product info page (/{locale}/products/{route}), edited in the admin panel:
     * content = {uz|ru|en: {about: html, claim: html, faq: [{q, a}]}}; rules_* = insurance rules PDF.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('content')->nullable()->after('desc_en');
            $table->string('rules_uz')->nullable()->after('offerta_en');
            $table->string('rules_ru')->nullable()->after('rules_uz');
            $table->string('rules_en')->nullable()->after('rules_ru');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['content', 'rules_uz', 'rules_ru', 'rules_en']);
        });
    }
};
