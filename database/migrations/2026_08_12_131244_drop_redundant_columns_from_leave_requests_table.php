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
            $table->dropColumn([
                'employee_name',
                'type',
                'reason',
                'attachment',
                'processed_by'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('employee_name')->nullable();
            $table->string('type')->nullable();
            $table->text('reason')->nullable();
            $table->string('attachment')->nullable();
            $table->string('processed_by')->nullable();
        });
    }
};
