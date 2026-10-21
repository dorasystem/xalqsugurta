<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Company / disclosure pages (/{locale}/info/{key}), imported from the old site and edited in the admin panel */
    public function up(): void
    {
        Schema::create('info_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('section', 20)->index();     // about | shareholders | useful
            $table->unsignedSmallInteger('sort_order')->default(0);
            foreach (['uz', 'ru', 'en'] as $l) {
                $table->string("title_{$l}")->nullable();
                $table->longText("body_{$l}")->nullable();
            }
            $table->boolean('is_published')->default(false);
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_pages');
    }
};
