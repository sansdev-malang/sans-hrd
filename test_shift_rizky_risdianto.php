<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = \App\Models\EmployeeWorkingShift::with('workingShift')->where('employee_id', 105)->get();
echo "Rizky Risdianto Shifts:\n";
foreach($s as $x) {
    echo $x->workingShift->name . ' : ' . substr($x->start_date, 0, 10) . ' to ' . substr($x->end_date, 0, 10) . "\n";
}
