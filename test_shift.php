<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$em = App\Models\EmployeeWorkingShift::with('workingShift.details')->get();
print_r($em->toArray());
