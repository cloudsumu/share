<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Provider::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->unique()->safeEmail,
            'password'   => Hash::make('password'),
            'mobile'     => $this->faker->unique()->phoneNumber,
            'latitude'   => $this->faker->latitude(-90, 90),
            'longitude'  => $this->faker->longitude(-90, 90),
        ];
    }
}
