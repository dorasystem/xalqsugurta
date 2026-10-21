<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The admin panel filters orders by status, date, phone and product on every page (tabs,
 * navigation badges, dashboard). orders had no index at all, and the product lived only
 * inside the insurances_data text: product_key is now a column of its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('product_key', 30)->nullable()->after('status');

            $table->index(['status', 'created_at']);
            $table->index('created_at');
            $table->index('phone');
            $table->index(['product_key', 'status']);
        });

        // Existing orders: copy _product_key out of insurances_data
        DB::table('orders')->select(['id', 'insurances_data'])->orderBy('id')->chunkById(500, function ($orders) {
            foreach ($orders as $order) {
                $key = json_decode((string) $order->insurances_data, true)['_product_key'] ?? null;

                if (is_string($key) && $key !== '') {
                    DB::table('orders')->where('id', $order->id)->update(['product_key' => substr($key, 0, 30)]);
                }
            }
        });

        Schema::table('api_logs', function (Blueprint $table) {
            $table->index('endpoint');
        });
    }

    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropIndex(['endpoint']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['product_key', 'status']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropColumn('product_key');
        });
    }
};
