<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Insured-event reports and call-back requests from the site. The insurer has no API for
     * them, so they are handled by staff in the admin panel (Murojaatlar).
     */
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product', 30)->nullable();
            $table->string('policy_number', 40);
            $table->string('full_name', 150);
            $table->string('phone', 12)->index();
            $table->date('event_date');
            $table->text('description');
            $table->json('files')->nullable();              // [{path, name, size}] on the private disk
            $table->string('status', 20)->default('new')->index();
            $table->text('public_note')->nullable();       // shown to the customer on the status page
            $table->text('internal_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('locale', 2)->default('uz');
            $table->timestamps();
        });

        Schema::create('callback_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 12);
            $table->string('topic', 30)->nullable();
            $table->string('message', 1000)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->string('locale', 2)->default('uz');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_requests');
        Schema::dropIfExists('claims');
    }
};
