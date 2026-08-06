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
        Schema::create('performance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 9); // Contoh: "2025/2026"
            $table->enum('semester', ['1', '2']);
            $table->unsignedBigInteger('employee_id'); // ID dari unit sekolah
            $table->foreignId('unit_id')->constrained('school_units')->onDelete('cascade');
            $table->decimal('score_pedagogik', 5, 2)->default(0.00);
            $table->decimal('score_kepribadian', 5, 2)->default(0.00);
            $table->decimal('score_sosial', 5, 2)->default(0.00);
            $table->decimal('score_profesional', 5, 2)->default(0.00);
            $table->decimal('score_discipline', 5, 2)->default(0.00);
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->string('predicate', 50)->nullable(); // Contoh: "Amat Baik"
            $table->text('recommendations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reports');
    }
};
