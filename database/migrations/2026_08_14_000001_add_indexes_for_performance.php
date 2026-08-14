<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes for frequently queried columns to improve performance.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Index for filtering by status and unit
            if (!Schema::hasIndex('leave_requests', 'idx_status_unit')) {
                $table->index(['status', 'school_unit_id'], 'idx_status_unit');
            }

            // Index for employee lookups
            if (!Schema::hasIndex('leave_requests', 'idx_employee_unit')) {
                $table->index(['employee_id', 'school_unit_id'], 'idx_employee_unit');
            }

            // Index for date range queries
            if (!Schema::hasIndex('leave_requests', 'idx_start_end_date')) {
                $table->index(['start_date', 'end_date'], 'idx_start_end_date');
            }

            // Index for sync queries
            if (!Schema::hasIndex('leave_requests', 'idx_school_unit')) {
                $table->index('school_unit_id', 'idx_school_unit');
            }
        });

        // Index school units for API token verification
        Schema::table('school_units', function (Blueprint $table) {
            if (!Schema::hasIndex('school_units', 'idx_api_token')) {
                $table->index('api_token', 'idx_api_token');
            }
            if (!Schema::hasIndex('school_units', 'idx_is_active')) {
                $table->index('is_active', 'idx_is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_status_unit');
            $table->dropIndex('idx_employee_unit');
            $table->dropIndex('idx_start_end_date');
            $table->dropIndex('idx_school_unit');
        });

        Schema::table('school_units', function (Blueprint $table) {
            $table->dropIndex('idx_api_token');
            $table->dropIndex('idx_is_active');
        });
    }
};
