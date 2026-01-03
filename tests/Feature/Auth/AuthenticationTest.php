<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => bcrypt('password'),
        ]);

        return [$user, $tenant];
    }

    public function test_users_can_authenticate_using_login_screen()
    {
        [$user, $tenant] = $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    public function test_users_cannot_authenticate_with_invalid_password()
    {
        [$user, $tenant] = $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Credenciais inválidas']);
    }

    public function test_users_can_logout()
    {
        [$user, $tenant] = $this->createUser();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/logout');

        $response->assertStatus(204);
    }
}
