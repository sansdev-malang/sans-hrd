<?php

namespace Tests\Feature;

use App\Models\SchoolUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmployeeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_employees_index()
    {
        $response = $this->get(route('employees.index'));
        $response->assertRedirect('/login');
    }

    public function test_hrd_user_can_access_employees_index()
    {
        $user = User::factory()->create(['role' => 'hrd']);
        
        // Mock API call within SchoolUnitService
        Http::fake([
            '*/employees' => Http::response(['success' => true, 'data' => []])
        ]);

        $response = $this->actingAs($user)->get(route('employees.index'));
        $response->assertStatus(200);
    }

    public function test_hrd_user_can_add_employee_to_unit()
    {
        $user = User::factory()->create(['role' => 'hrd']);
        
        $unit = SchoolUnit::create([
            'name' => 'SD Unit',
            'api_url' => 'http://sansdev.test/api/v1/hrd',
            'api_token' => 'rahasia_sd_123',
            'is_active' => true,
        ]);

        Http::fake([
            'http://sansdev.test/api/v1/hrd/employees' => Http::response([
                'success' => true,
                'data' => ['id' => 99, 'name' => 'Pegawai Baru']
            ], 201)
        ]);

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'school_unit_id' => $unit->id,
            'name' => 'Pegawai Baru',
            'email' => 'new.emp@sans.dev',
            'nuptk_nip_nik' => '1234567890',
            'employee_type_code' => 'teacher',
            'subject_position' => 'Biologi',
            'gender' => 'Male',
            'employment_status' => 'Honorer',
            'zkteco_uid' => '201',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success');
    }
}
