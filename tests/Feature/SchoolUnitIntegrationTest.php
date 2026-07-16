<?php

namespace Tests\Feature;

use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SchoolUnitIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_employees_via_service()
    {
        // Create a SchoolUnit in the test database
        SchoolUnit::create([
            'name' => 'SD Unit',
            'api_url' => 'http://localhost:8000/api/v1/hrd',
            'api_token' => 'rahasia_sd_123',
            'is_active' => true,
        ]);

        Http::fake([
            'http://localhost:8000/api/v1/hrd/employees' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Guru Tester',
                        'unit' => 'sd',
                        'subject_position' => 'Matematika'
                    ]
                ]
            ], 200)
        ]);

        $service = new SchoolUnitService();
        $employees = $service->getSdEmployees();

        $this->assertCount(1, $employees);
        $this->assertEquals('Guru Tester', $employees[0]['name']);
        $this->assertEquals('SD Unit', $employees[0]['unit_name']);
    }

    public function test_can_fetch_attendances_via_service()
    {
        // Create a SchoolUnit in the test database
        SchoolUnit::create([
            'name' => 'SD Unit',
            'api_url' => 'http://localhost:8000/api/v1/hrd',
            'api_token' => 'rahasia_sd_123',
            'is_active' => true,
        ]);

        $date = '2026-07-16';

        Http::fake([
            'http://localhost:8000/api/v1/hrd/attendances*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 10,
                        'employee_id' => 1,
                        'date' => $date,
                        'status' => 'Present'
                    ]
                ]
            ], 200)
        ]);

        $service = new SchoolUnitService();
        $attendances = $service->getSdAttendances($date);

        $this->assertCount(1, $attendances);
        $this->assertEquals('Present', $attendances[0]['status']);
        $this->assertEquals('SD Unit', $attendances[0]['unit_name']);
    }
}
