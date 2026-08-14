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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate syncs from same unit
            if (!Schema::hasIndex('leave_requests', 'unique_remote_per_unit')) {
                $table->unique(['remote_leave_id', 'school_unit_id'], 'unique_remote_per_unit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropUnique('unique_remote_per_unit');
        });
    }
};
