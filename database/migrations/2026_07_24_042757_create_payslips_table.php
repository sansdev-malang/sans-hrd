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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // Remote employee ID on the unit
            $table->foreignId('school_unit_id')->constrained('school_units')->onDelete('cascade');
            $table->string('period', 10); // e.g. 2026-07
            $table->string('file_path');
            $table->timestamps();
            
            // Allow an employee to only have one payslip per period in a given unit
            $table->unique(['employee_id', 'school_unit_id', 'period'], 'payslip_emp_unit_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
