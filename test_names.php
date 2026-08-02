<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\SchoolUnitService::class);
$emps = $svc->getSdEmployees();
print_r(collect($emps)->whereIn('id', [105, 107])->pluck('name', 'id')->toArray());
