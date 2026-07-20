<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\ZktecoDevice;
use Rats\Zkteco\Lib\ZKTeco;
use Illuminate\Support\Facades\Log;

class ZktecoService
{
    /**
     * Pull attendance logs from a specific device.
     */
    public function pullLogs(ZktecoDevice $device)
    {
        $zk = new ZKTeco($device->ip_address, $device->port);
        
        if ($zk->connect()) {
            try {
                $attendances = $zk->getAttendance();
                $users = $zk->getUser(); // Tarik data user untuk ambil nama lokal

                // Mapping UserID (PIN) to Name
                $localUserMap = [];
                if (is_array($users)) {
                    foreach ($users as $u) {
                        if (isset($u['userid'])) {
                            $localUserMap[(string)$u['userid']] = $u['name'] ?? null;
                        }
                    }
                }

                $rawCount = is_array($attendances) ? count($attendances) : 0;
                $count = 0;

                if ($rawCount > 0) {
                    foreach ($attendances as $record) {
                        // Gunakan 'id' karena itu adalah User ID/PIN bertipe string
                        $userId = (string)$record['id']; 

                        // Avoid duplicates by checking existing record
                        $exists = AttendanceLog::where('zkteco_device_id', $device->id)
                            ->where('uid', $userId)
                            ->where('timestamp', $record['timestamp'])
                            ->exists();

                        if (!$exists) {
                            AttendanceLog::create([
                                'zkteco_device_id' => $device->id,
                                'uid'              => $userId,
                                'timestamp'        => $record['timestamp'],
                                'state'            => $record['state'] ?? null,
                                'type'             => $record['type'] ?? null,
                                'local_name'       => $localUserMap[$userId] ?? null,
                            ]);
                            $count++;
                        }
                    }
                }

                // Update device last_sync_at
                $device->update(['last_sync_at' => now(), 'is_online' => true]);
                
                $zk->disconnect();
                
                return [
                    'success' => true,
                    'count' => $count,
                    'message' => "Koneksi sukses! Ditemukan {$rawCount} log di memori mesin. {$count} data baru berhasil disimpan."
                ];
                
            } catch (\Exception $e) {
                $zk->disconnect();
                Log::error("ZktecoService Error: " . $e->getMessage());
                return [
                    'success' => false,
                    'count' => 0,
                    'message' => 'Terjadi kesalahan saat menarik data: ' . $e->getMessage()
                ];
            }
        }

        $device->update(['is_online' => false]);
        return [
            'success' => false,
            'count' => 0,
            'message' => 'Koneksi ke mesin gagal.'
        ];
    }
}
