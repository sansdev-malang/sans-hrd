<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$endDate = \Carbon\Carbon::parse('2026-08-26 23:59:59');
$lastDay = clone $endDate;
if ($endDate > now()) {
    $lastDay = now()->endOfDay();
}
$currentDate = \Carbon\Carbon::parse('2026-07-27 00:00:00');
$keys=[];
while($currentDate <= $lastDay) {
    $keys[] = $currentDate->format('Y-m-d');
    $currentDate->addDay();
}
print_r($keys);
