<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate()
    {
        $tenant = Tenant::factory()->create();

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $token = $user->createToken('api-token')->plainTextToken;

        return [$user, $tenant, $token];
    }

    public function test_can_create_service()
    {
        [$user, $tenant, $token] = $this->authenticate();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/services', [
            'name' => 'Corte de cabelo',
            'duration' => 30,
            'price' => 50.0,
            'tenant_id' => $tenant->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Corte de cabelo',
                'duration' => 30,
                'price' => 50.0,
            ]);
    }

    public function test_can_list_services()
    {
        [$user, $tenant, $token] = $this->authenticate();

        Service::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Serviço A']);
        Service::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Serviço B']);

        $otherTenant = Tenant::factory()->create();
        Service::factory()->create(['tenant_id' => $otherTenant->id, 'name' => 'Outro Tenant']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/services');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Serviço A'])
            ->assertJsonFragment(['name' => 'Serviço B']);
    }

    public function test_can_update_service()
    {
        [$user, $tenant, $token] = $this->authenticate();

        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Nome Antigo']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson("/api/services/{$service->id}", [
            'name' => 'Novo Nome',
        ]);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Novo Nome']);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Novo Nome',
        ]);
    }

    public function test_can_delete_service()
    {
        [$user, $tenant, $token] = $this->authenticate();

        $service = Service::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_cannot_access_service_of_other_tenant()
    {
        [$user, $tenant, $token] = $this->authenticate();

        $otherTenant = Tenant::factory()->create();
        $service = Service::factory()->create(['tenant_id' => $otherTenant->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson("/api/services/{$service->id}");

        $response->assertStatus(403);
    }
}
