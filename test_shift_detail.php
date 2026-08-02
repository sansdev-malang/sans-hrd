<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = \App\Models\EmployeeWorkingShift::with('workingShift.details')->where('employee_id', 102)->get();
print_r($s->toArray());
