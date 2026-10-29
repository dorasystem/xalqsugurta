<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Branches (/{locale}/branches), edited in the admin panel; BranchSeeder holds the old site's list */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            foreach (['uz', 'ru', 'en'] as $l) {
                $table->string("name_{$l}")->nullable();
                $table->string("address_{$l}", 500)->nullable();
            }
            $table->string('manager')->nullable();
            $table->json('phones')->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
