<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every request to the insurer's API (see App\Services\ApiLogger); pruned after 30 days
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product', 30)->nullable();
            $table->string('method', 10);
            $table->string('endpoint', 150);
            $table->string('url', 500);
            $table->unsignedSmallInteger('status')->nullable();
            $table->string('result', 30)->nullable();
            $table->boolean('success')->default(false);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('request')->nullable();
            $table->mediumText('response')->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamps();

            $table->index(['success', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
