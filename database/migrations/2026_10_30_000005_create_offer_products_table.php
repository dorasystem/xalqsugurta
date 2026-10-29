<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Products sold on application, not online (/{locale}/insurance/{slug}): texts and the request form */
    public function up(): void
    {
        Schema::create('offer_products', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('audience', 20)->default('private');
            foreach (['uz', 'ru', 'en'] as $l) {
                $table->string("title_{$l}")->nullable();
                $table->string("kicker_{$l}")->nullable();
                $table->text("lead_{$l}")->nullable();
                $table->text("highlights_{$l}")->nullable();
                $table->text("covers_{$l}")->nullable();
                $table->text("terms_{$l}")->nullable();
                $table->text("note_{$l}")->nullable();
            }
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_products');
    }
};
