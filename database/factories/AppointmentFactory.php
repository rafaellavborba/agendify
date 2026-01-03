<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $service = Service::factory()->create();
        $startTime = $this->faker->time('H:i', '17:00');
        $duration = $service->duration;

        return [
            'tenant_id' => $service->tenant_id,
            'user_id' => User::factory(['tenant_id' => $service->tenant_id]),
            'service_id' => $service->id,
            'client_name' => $this->faker->name,
            'client_phone' => $this->faker->phoneNumber,
            'date' => $this->faker->date(),
            'start_time' => $startTime,
            'end_time' => date('H:i', strtotime("$startTime +$duration minutes")),
            'status' => 'pending',
        ];
    }
}
