<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ctrl = app(\App\Http\Controllers\AttendanceLogController::class);

$req = new \Illuminate\Http\Request();
$req->query->set('month', '2026-08');
$req->query->set('per_page', 'all');
$res = $ctrl->index($req);
$data = $res->getData();

$reports = $data['paginatedReports']->items();
$hasData = 0;
foreach ($reports as $r) {
    if (isset($r['daily_details']['2026-08-02'])) {
        $hasData++;
    }
}
echo "Total employees: " . count($reports) . "\n";
echo "Employees with data on 2026-08-02: " . $hasData . "\n";
