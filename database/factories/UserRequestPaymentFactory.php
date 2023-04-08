<?php

namespace Database\Factories;

use App\Models\UserRequest;
use App\Models\UserRequestPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserRequestPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserRequestPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'request_id' => 1,
            'user_id' => 1,
            'provider_id' => 1,
            'provider_pay' => rand(1, 9)*10,
        ];
    }
}