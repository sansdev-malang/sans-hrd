<?php

namespace Tests\Feature;

use App\Models\SchoolUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolUnitCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_school_units()
    {
        $response = $this->get(route('school-units.index'));
        $response->assertRedirect('/login');
    }

    public function test_non_hrd_user_cannot_access_school_units()
    {
        $user = User::factory()->create(['role' => 'employee']);
        $response = $this->actingAs($user)->get(route('school-units.index'));
        $response->assertStatus(403);
    }

    public function test_hrd_user_can_access_school_units()
    {
        $user = User::factory()->create(['role' => 'hrd']);
        $response = $this->actingAs($user)->get(route('school-units.index'));
        $response->assertStatus(200);
    }

    public function test_hrd_user_can_create_school_unit()
    {
        $user = User::factory()->create(['role' => 'hrd']);
        
        $response = $this->actingAs($user)->post(route('school-units.store'), [
            'name' => 'SMP Unit',
            'api_url' => 'http://localhost:8002/api/v1/hrd',
            'api_token' => 'token_smp_123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('school-units.index'));
        $this->assertDatabaseHas('school_units', [
            'name' => 'SMP Unit',
            'api_url' => 'http://localhost:8002/api/v1/hrd',
        ]);
    }
}
