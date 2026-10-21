<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin overrides of the controller FLOW constants (rate, sums, presets, term); see ProductSettings
        Schema::table('products', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('sort_order');
        });

        Schema::create('product_setting_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('field', 50);
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_setting_changes');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
