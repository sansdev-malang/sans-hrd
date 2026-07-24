<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\ZktecoDevice;
use App\Services\BioTimeService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('zkteco:pull')]
#[Description('Pull attendance logs from ZKBio Time API')]
class PullZktecoLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BioTimeService $service)
    {
        $this->info("Authenticating with ZKBio Time...");
        if (!$service->authenticate()) {
            $this->error("Failed to authenticate with ZKBio Time API. Check credentials.");
            return;
        }
        $this->info("Authenticated successfully!");

        // Pull from 7 days ago to ensure all recent data is synced cleanly
        $startTime = Carbon::now()->subDays(7)->format('Y-m-d 00:00:00');
        $endTime = Carbon::now()->format('Y-m-d H:i:s');
        
        $this->info("Pulling transactions from $startTime to $endTime...");
        
        $transactions = $service->getTransactions($startTime, $endTime);
        
        if (empty($transactions)) {
            $this->info("No new transactions found or error occurred.");
            return;
        }
        
        $this->info("Found " . count($transactions) . " records. Syncing to local DB...");

        // Usually ZKBio Time API returns transactions with fields like:
        // emp_code (UID), punch_time, punch_state, terminal_sn
        // We'll iterate and insert safely.

        $newCount = 0;
        
        // Cache devices by serial number or alias
        // Assuming the device in our DB matches ZKBio Time either by IP, name or we just use a default
        $defaultDevice = ZktecoDevice::first(); 
        if (!$defaultDevice) {
            $defaultDevice = ZktecoDevice::create([
                'name' => 'ZKBioTime Default API',
                'ip_address' => 'api.biotime',
                'port' => 80,
                'model_name' => 'API',
                'is_online' => true,
                'sync_interval' => 5
            ]);
        }
        
        foreach ($transactions as $txn) {
            // ZKBioTime API transaction format might vary, often:
            // emp_code (string), punch_time (string Y-m-d H:i:s), punch_state (string/int), terminal_alias (string)
            $uid = $txn['emp_code'] ?? null;
            $timestamp = $txn['punch_time'] ?? null;
            $state = $txn['punch_state'] ?? null;
            
            if (!$uid || !$timestamp) {
                continue; // Skip invalid
            }
            
            // Format punch time safely
            try {
                $punchTime = Carbon::parse($timestamp)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                continue;
            }

            // Check if exists
            $exists = AttendanceLog::where('uid', $uid)
                        ->where('timestamp', $punchTime)
                        ->exists();
                        
            if (!$exists) {
                AttendanceLog::create([
                    'zkteco_device_id' => $defaultDevice ? $defaultDevice->id : 1, // Fallback if no device
                    'uid' => $uid,
                    'timestamp' => $punchTime,
                    'state' => is_numeric($state) ? (int)$state : 15,
                    'type' => 255, // default
                    'local_name' => null // API might not send local_name, we can ignore or fetch
                ]);
                $newCount++;
            }
        }
        
        $this->info("Sync complete. $newCount new records added.");
        
        // Update device sync timestamp
        if ($defaultDevice) {
            $defaultDevice->update(['last_sync_at' => now(), 'is_online' => true]);
        }
    }
}
