<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ctrl = app(\App\Http\Controllers\DashboardController::class);
$res = $ctrl->index(request());
$data = $res->getData();
var_dump($data['attendanceMap'][13] ?? 'Not in map');
var_dump($data['attendanceMap']);
