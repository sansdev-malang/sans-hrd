<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\SchoolUnitService::class);
$emps = $svc->getSdEmployees();
$name = collect($emps)->firstWhere('id', 13)['name'] ?? 'Pegawai';
echo $name;
