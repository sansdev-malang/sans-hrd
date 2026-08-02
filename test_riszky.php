<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\SchoolUnitService::class);
$emps = $service->getSdEmployees();
foreach ($emps as $e) {
    if (stripos($e['name'], 'riszky') !== false || stripos($e['name'], 'rizky') !== false) {
        echo $e['id'] . ' - ' . $e['name'] . "\n";
    }
}
