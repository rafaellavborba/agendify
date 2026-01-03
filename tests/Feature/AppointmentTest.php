<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate()
    {
        $tenant = Tenant::factory()->create();

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $service = Service::factory()->create(['tenant_id' => $tenant->id, 'duration' => 30]);

        $token = $user->createToken('api-token')->plainTextToken;

        return [$user, $service, $tenant, $token];
    }

    public function test_create_appointment_success()
    {
        [$user, $service, $tenant, $token] = $this->authenticate();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/appointments', [
            'service_id' => $service->id,
            'client_name' => 'Maria Silva',
            'client_phone' => '11999999999',
            'date' => '2026-01-10',
            'start_time' => '14:00',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'client_name' => 'Maria Silva',
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('appointments', [
            'client_name' => 'Maria Silva',
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_create_appointment_conflict()
    {
        [$user, $service, $tenant, $token] = $this->authenticate();

        Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
            'client_name' => 'Existing Client',
            'date' => '2026-01-10',
            'start_time' => '14:00',
            'end_time' => '14:30',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/appointments', [
            'service_id' => $service->id,
            'client_name' => 'Maria Silva',
            'client_phone' => '11999999999',
            'date' => '2026-01-10',
            'start_time' => '14:15',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'start_time' => ['Já existe um agendamento nesse horário.']
            ]);
    }

    public function test_list_appointments()
    {
        [$user, $service, $tenant, $token] = $this->authenticate();

        Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
            'client_name' => 'Cliente A',
            'date' => '2026-01-10',
            'start_time' => '10:00',
            'end_time' => '10:30',
        ]);

        Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
            'client_name' => 'Cliente B',
            'date' => '2026-01-10',
            'start_time' => '11:00',
            'end_time' => '11:30',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/appointments');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['client_name' => 'Cliente A'])
            ->assertJsonFragment(['client_name' => 'Cliente B']);

    }

    public function test_show_appointment()
    {
        [$user, $service, $tenant, $token] = $this->authenticate();

        $appointment = Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $appointment->id,
                'tenant_id' => $tenant->id,
            ]);
    }

    public function test_update_appointment()
    {
        [$user, $service, $tenant, $token] = $this->authenticate();

        $appointment = Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
            'client_name' => 'Old Name',
            'date' => '2026-01-10',
            'start_time' => '12:00',
            'end_time' => '12:30',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson("/api/appointments/{$appointment->id}", [
            'client_name' => 'New Name',
            'start_time' => '13:00',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['client_name' => 'New Name']);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'client_name' => 'New Name',
            'start_time' => '13:00',
        ]);
    }

    public function test_delete_appointment()
    {
        [$user, $service, $tenant, $token] = $this->authenticate();

        $appointment = Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);
    }

    public function test_cannot_access_other_tenant_appointment()
    {
        [$user, $service, $tenant, $token] = $this->authenticate();

        $otherTenant = Tenant::factory()->create();

        $appointment = Appointment::factory()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(403);
    }
}
