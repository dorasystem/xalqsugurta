<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Server commands started from the admin panel (Tizim → Server buyruqlari) and their output */
    public function up(): void
    {
        Schema::create('command_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command', 40);
            $table->string('status', 20)->default('running')->index();
            $table->longText('output')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('command_runs');
    }
};
