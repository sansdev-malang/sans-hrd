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
        Schema::table('zkteco_devices', function (Blueprint $table) {
            $table->integer('sync_interval')->default(5)->after('is_online');
            $table->dateTime('last_sync_at')->nullable()->after('sync_interval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zkteco_devices', function (Blueprint $table) {
            $table->dropColumn(['sync_interval', 'last_sync_at']);
        });
    }
};
