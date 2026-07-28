<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

$data = [
    'app_name' => 'SANS HRD',
    'unit_name' => 'Yayasan Pendidikan Anak Saleh',
    'app_email' => 'admin@sans.dev',
    'app_phone' => '+62 812-3456-7890',
    'app_address' => 'Jl. Candi Panggung Indah 1-3 Kota Malang, Jawa Timur 65139',
    'app_copyright' => '© ' . date('Y') . ' Yayasan Pendidikan Anak Saleh. All rights reserved.',
];

foreach ($data as $key => $value) {
    Setting::updateOrCreate(['key' => $key], ['value' => $value]);
}

echo "Settings updated successfully in HRD database.\n";
