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
$today = '2026-08-02';

foreach ($reports as $r) {
    $empId = $r['employee']['id'];
    if (in_array($empId, [105])) {
        echo "Employee {$r['employee']['name']} (ID: $empId) - $today: ";
        if (isset($r['daily_details'][$today])) {
            echo json_encode($r['daily_details'][$today]) . "\n";
        } else {
            echo "NO DATA\n";
        }
    }
}
