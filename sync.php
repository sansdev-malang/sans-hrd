<?php
// Hapus device lama
\App\Models\ZktecoDevice::find(1)?->delete();

$units = \App\Models\SchoolUnit::whereIn('id', [2, 3])->get();
$deviceIds = [2, 3];
$totalSynced = 0;

foreach ($units as $unit) {
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'X-API-TOKEN' => $unit->api_token,
        'Accept' => 'application/json',
    ])->get(rtrim($unit->api_url, '/') . '/employees');
    
    if ($response->successful()) {
        $employees = $response->json('data') ?? [];
        foreach ($employees as $emp) {
            $zkteco_uid = $emp['zkteco_uid'] ?? null;
            if (!empty($zkteco_uid)) {
                \App\Models\EmployeeDeviceMapping::where('zkteco_uid', $zkteco_uid)->delete();
                foreach ($deviceIds as $deviceId) {
                    \App\Models\EmployeeDeviceMapping::create([
                        'zkteco_uid' => $zkteco_uid,
                        'zkteco_device_id' => $deviceId
                    ]);
                    \App\Models\AdmsCommand::create([
                        'zkteco_device_id' => $deviceId,
                        'command_string' => "DATA UPDATE USERINFO PIN={$zkteco_uid}\tName={$emp['name']}"
                    ]);
                }
                $totalSynced++;
            }
        }
    }
}
echo "Total disinkronkan: " . $totalSynced . "\n";
