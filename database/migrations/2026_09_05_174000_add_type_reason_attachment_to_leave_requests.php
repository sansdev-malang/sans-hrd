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
            if (!Schema::hasColumn('leave_requests', 'type')) {
                $table->string('type')->nullable()->after('status_code');
            }
            if (!Schema::hasColumn('leave_requests', 'reason')) {
                $table->text('reason')->nullable()->after('type');
            }
            if (!Schema::hasColumn('leave_requests', 'attachment')) {
                $table->string('attachment')->nullable()->after('reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['type', 'reason', 'attachment']);
        });
    }
};
