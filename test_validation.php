<?php
require __DIR__.'/vendor/autoload.php';
\ = require_once __DIR__.'/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = Illuminate\Http\Request::create('/employees/1/1', 'PUT', [
    'employee_type_code' => 'TENDIK',
    'name' => 'Test Name',
    'gender' => 'L',
    'front_title' => 'Dr. Ir. H. Raden Mas Ngabehi Sosrodiningrat Ketiga Belas, M.Sc., Ph.D.',
]);
\ = app(\App\Http\Controllers\EmployeeController::class);
try {
    \->update(\, 1, 1);
    echo 'PASSED';
} catch (\Illuminate\Validation\ValidationException \) {
    echo 'VALIDATION FAILED: ' . json_encode(\->errors());
} catch (\Exception \) {
    echo 'OTHER EXCEPTION: ' . \->getMessage();
}
