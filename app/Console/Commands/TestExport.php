<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestExport extends Command {
    protected $signature = 'test:export';
    public function handle() {
        $req = Request::create('/employee-working-shifts/export-roster', 'GET', [
            'unit_id' => 1, 'month' => 7, 'year' => 2026, 'type' => 'pdf'
        ]);
        $app = app();
        $controller = $app->make(\App\Http\Controllers\EmployeeWorkingShiftController::class);
        try {
            $res = $controller->exportRoster($req);
            $this->info("Status: " . $res->getStatusCode());
            if ($res->getStatusCode() !== 200) {
                $this->error(substr($res->getContent(), 0, 500));
            } else {
                $this->info("Content Length: " . strlen($res->getContent()));
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
