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
        Schema::table('employee_working_shifts', function (Blueprint $table) {
            $table->foreignId('bonus_schema_id')->nullable()->constrained('bonus_schemas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_working_shifts', function (Blueprint $table) {
            $table->dropForeign(['bonus_schema_id']);
            $table->dropColumn('bonus_schema_id');
        });
    }
};
