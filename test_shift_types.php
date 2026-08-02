<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\EmployeeWorkingShift::count();
echo $count . " total shift assignments\n";

$shiftTypes = \App\Models\WorkingShift::all();
foreach ($shiftTypes as $st) {
    echo $st->name . " (is_shift: " . $st->is_shift . ")\n";
}
