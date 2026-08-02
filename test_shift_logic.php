<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ctrl = app(\App\Http\Controllers\DashboardController::class);
$res = $ctrl->index(request());
$data = $res->getData();

$atts = $data['attendanceMap'];
foreach ($atts as $key => $val) {
    if ($key == "1_102" || $key == "2_102") echo "Abdul Khodir ($key): "; // Might be Unit 1 or 2
    elseif ($key == "1_105" || $key == "2_105") echo "Heri ($key): ";
    elseif ($key == "1_107" || $key == "2_107") echo "Rizky ($key): ";
    elseif ($key == "3_13") echo "Ihwan ($key): ";
    else continue;
    
    echo json_encode($val) . "\n";
}
