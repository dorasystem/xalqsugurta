<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The body exactly as sent. `request` is a JSON column: MySQL sorts its keys and
     * stores ‘ as ‘, so it cannot show what the insurer actually received.
     */
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->mediumText('request_raw')->nullable()->after('request');
        });
    }

    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropColumn('request_raw');
        });
    }
};
