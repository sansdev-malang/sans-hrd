<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('category')->default('umum'); // 'umum', 'akademik', 'kepegawaian', 'penting'
            $table->timestamp('publish_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('attachment')->nullable();
            $table->json('target_units')->nullable(); // stores array of [{"school_unit_id": id, "target_audience": audience}]
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
