<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_the_application_redirects_guest_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_hrd_user_can_access_dashboard(): void
    {
        $user = \App\Models\User::factory()->create(['role' => 'hrd']);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
