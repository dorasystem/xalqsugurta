<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tenders the company announces (/{locale}/tenders), edited in the admin panel */
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            foreach (['uz', 'ru', 'en'] as $l) {
                $table->string("title_{$l}")->nullable();
                $table->longText("body_{$l}")->nullable();
            }
            $table->string('email')->nullable();
            $table->date('published_at');
            $table->date('deadline_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
