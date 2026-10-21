<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A click_uzs row is one Click transaction; its id is the merchant_prepare_id we return in Prepare
        Schema::table('click_uzs', function (Blueprint $table) {
            $table->string('click_paydoc_id')->nullable()->after('click_trans_id');
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->index('click_trans_id');
        });
    }

    public function down(): void
    {
        Schema::table('click_uzs', function (Blueprint $table) {
            $table->dropIndex(['click_trans_id']);
            $table->dropColumn(['click_paydoc_id', 'completed_at']);
        });
    }
};
