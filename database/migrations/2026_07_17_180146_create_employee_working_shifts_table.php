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
        Schema::create('employee_working_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // Remote employee ID on the unit
            $table->foreignId('school_unit_id')->constrained('school_units')->onDelete('cascade');
            $table->foreignId('working_shift_id')->constrained('working_shifts')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_working_shifts');
    }
};
