<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
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
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Corte de cabelo']);

        $this->assertDatabaseHas('services', ['name' => 'Corte de cabelo', 'tenant_id' => $tenant->id]);
    }

    public function test_can_list_services_with_filters_and_sort()
    {
        [$user, $tenant, $token] = $this->authenticate();

        Service::factory()->create(['tenant_id' => $tenant->id, 'name' => 'A Service', 'price' => 20]);
        Service::factory()->create(['tenant_id' => $tenant->id, 'name' => 'B Service', 'price' => 10]);
        Service::factory()->create(['tenant_id' => 999, 'name' => 'Other Tenant']); // não deve aparecer

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/services?sort_by=price&sort_order=desc&q=Service');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') 
            ->assertJsonFragment(['name' => 'A Service'])
            ->assertJsonFragment(['name' => 'B Service']);
    }

    public function test_can_update_service()
    {
        [$user, $tenant, $token] = $this->authenticate();

        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Old Name']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson("/api/services/{$service->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'New Name']);
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

        $service = Service::factory()->create(['tenant_id' => 999]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson("/api/services/{$service->id}");

        $response->assertStatus(403);
    }
}
