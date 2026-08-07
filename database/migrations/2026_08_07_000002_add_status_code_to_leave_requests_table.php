<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('status_code', 5)->nullable()->after('type');
            $table->boolean('gets_presence_bonus')->default(false)->after('status_code');
        });

        // Update existing records based on standard mappings
        DB::table('leave_requests')->where('type', 'Sakit')->update([
            'status_code' => 'S',
            'gets_presence_bonus' => false
        ]);
        DB::table('leave_requests')->where('type', 'Izin')->update([
            'status_code' => 'I',
            'gets_presence_bonus' => false
        ]);
        DB::table('leave_requests')->where('type', 'Cuti')->update([
            'status_code' => 'C',
            'gets_presence_bonus' => false
        ]);
        DB::table('leave_requests')->where('type', 'Dinas')->update([
            'status_code' => 'H',
            'gets_presence_bonus' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['status_code', 'gets_presence_bonus']);
        });
    }
};
