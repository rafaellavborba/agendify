<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'name' => $this->faker->word(),
            'duration' => $this->faker->numberBetween(15, 120), 
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
