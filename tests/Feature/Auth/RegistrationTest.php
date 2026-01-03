<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

   public function test_new_users_can_register(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'company_name' => 'Test Company',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'tenant' => ['id', 'name', 'created_at', 'updated_at'],
                    'user' => ['id', 'name', 'email', 'tenant_id', 'created_at', 'updated_at'],
                    'token',
                ]);
    }

}
