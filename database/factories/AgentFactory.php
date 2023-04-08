<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'     => $this->faker->firstName,
            'email'    => preg_replace('/@example\..*/', '.agent@dragon.com', $this->faker->unique()->safeEmail),
            'password' => Hash::make('password'),
            'mobile'   => $this->faker->unique()->phoneNumber,
        ];
    }
}
