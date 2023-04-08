<?php

namespace Database\Factories;

use App\Models\GeoFencing;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeoFencingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GeoFencing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'city_name' => $this->faker->city,
            'ranges' => '[{"lat":"-58.801519","lng":"128.609325"},{"lat":"-65.936851","lng":"-47.171925"},{"lat":"40.997712","lng":"-118.890675"},{"lat":"54.991155","lng":"49.859325"},{"lat":"64.178388","lng":"124.390575"}]'
        ];
    }
}
