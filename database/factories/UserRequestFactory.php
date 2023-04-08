<?php

namespace Database\Factories;

use App\Models\UserRequest;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'booking_id' => Str::random(10),
            'user_id' => rand(1,20),
            'provider_id' => rand(1,20),
            'geo_fencing_id' => rand(1,10),
            'service_type_id' => rand(1,12),
            'status' => $this->faker->randomElement(['CANCELLED', 'COMPLETED']),
            'cancelled_by' => $this->faker->randomElement(['NONE', 'USER', 'PROVIDER']),
            'payment_mode' => 'CASH',
            'estimated_fare' => rand(50,100),
            'distance' => rand(1, 15),
            's_latitude' => '12.14141',
            'invoice_item' => 1,
            's_longitude' => '12.14141',
            'route_key' => Str::random(40),
            'created_at' => $this->faker->dateTimeBetween('-4 months'),
        ];
    }
}
