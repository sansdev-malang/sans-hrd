<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('employee_working_shifts') && Schema::hasColumn('employee_working_shifts', 'bonus_schema_id')) {
            // Otomatis isi jadwal lama yang masih NULL ke skema Default (ID 1 - Toleransi Keterlambatan)
            DB::table('employee_working_shifts')
                ->whereNull('bonus_schema_id')
                ->update(['bonus_schema_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data backfill
    }
};
