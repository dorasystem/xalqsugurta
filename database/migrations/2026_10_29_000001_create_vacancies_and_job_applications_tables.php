<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Career section: vacancies edited in the admin panel and the CVs sent from the site */
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            foreach (['uz', 'ru', 'en'] as $l) {
                $table->string("title_{$l}")->nullable();
                $table->longText("body_{$l}")->nullable();
            }
            $table->string('location')->nullable();
            $table->string('employment', 20)->nullable();
            $table->date('published_at');
            $table->date('deadline_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('phone', 12)->index();
            $table->string('email')->nullable();
            $table->text('message')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name', 150)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->string('locale', 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('vacancies');
    }
};
