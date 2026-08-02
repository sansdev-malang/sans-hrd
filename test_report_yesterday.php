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
$yesterday = '2026-08-01';

foreach ($reports as $r) {
    $empId = $r['employee']['id'];
    if (in_array($empId, [102, 107])) {
        echo "Employee {$r['employee']['name']} (ID: $empId) - $yesterday: ";
        if (isset($r['daily_details'][$yesterday])) {
            echo json_encode($r['daily_details'][$yesterday]) . "\n";
        } else {
            echo "NO DATA\n";
        }
    }
}
