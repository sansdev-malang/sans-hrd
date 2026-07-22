<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\BioTimeService();
$service->authenticate();
$prop = new \ReflectionProperty($service, 'token');
$prop->setAccessible(true);
$token = $prop->getValue($service);

$response = \Illuminate\Support\Facades\Http::withToken($token)
    ->get('http://127.0.0.1:8080/iclock/api/transactions/?page=2&page_size=1000&punch_time__gte=2026-07-21+00%3A00%3A00&punch_time__lte=2026-07-22+23%3A59%3A59');
    
$json = $response->json();
echo "Count: " . ($json['count'] ?? 'N/A') . "\n";
echo "Next: " . ($json['next'] ?? 'N/A') . "\n";
