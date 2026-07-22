<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$txn = ['emp_code' => '123', 'punch_time' => '2026-07-22 06:52:35', 'punch_state' => 255]; 
$punchTime = \Carbon\Carbon::parse($txn['punch_time'])->format('Y-m-d H:i:s'); 
$exists = \App\Models\AttendanceLog::where('uid', '123')->where('timestamp', $punchTime)->exists();
echo "Exists? " . ($exists ? 'Yes' : 'No') . "\n";

if (!$exists) {
    try {
        $log = \App\Models\AttendanceLog::create([
            'zkteco_device_id' => 1,
            'uid' => '123',
            'timestamp' => $punchTime,
            'state' => 255,
            'type' => 255,
            'local_name' => 'Nova'
        ]);
        echo "Created log ID: " . $log->id . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
