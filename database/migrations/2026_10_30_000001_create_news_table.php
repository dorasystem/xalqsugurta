<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Press centre (/{locale}/news): news edited in the admin panel or imported from the old site */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            foreach (['uz', 'ru', 'en'] as $l) {
                $table->string("title_{$l}", 500)->nullable();
                $table->longText("body_{$l}")->nullable();
            }
            $table->string('image')->nullable();
            $table->dateTime('published_at');
            $table->boolean('is_published')->default(false);
            $table->string('source_url', 500)->nullable()->unique();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
