<?php

namespace App\Console\Commands;

use App\Models\ZktecoDevice;
use App\Services\ZktecoService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('zkteco:pull')]
#[Description('Pull attendance logs from ZKTeco devices based on their sync interval')]
class PullZktecoLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ZktecoService $service)
    {
        $devices = ZktecoDevice::all();

        foreach ($devices as $device) {
            $lastSync = $device->last_sync_at ? Carbon::parse($device->last_sync_at) : null;
            
            // If never synced, or last sync was longer than sync_interval minutes ago
            if (!$lastSync || $lastSync->diffInMinutes(now()) >= $device->sync_interval) {
                $this->info("Pulling logs for device: {$device->name} ({$device->ip_address})");
                $result = $service->pullLogs($device);
                
                if ($result['success']) {
                    $this->info($result['message']);
                } else {
                    $this->error($result['message']);
                }
            } else {
                $this->line("Skipping {$device->name} (Next sync in " . ($device->sync_interval - $lastSync->diffInMinutes(now())) . " min)");
            }
        }
    }
}
