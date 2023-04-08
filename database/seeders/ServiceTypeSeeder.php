<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('service_types')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('service_types')->insert([
            [
                'name'          => '{"en":"Sedan"}',
                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'minute'        => 0,
                'distance'      => '1',
                'calculator'    => 'DISTANCE',
                'image'         => 'public/asset/img/cars/sedan.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Hatchback"}',

                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'minute'        => 0,
                'distance'      => '1',
                'calculator'    => 'DISTANCE',
                'image'         => 'public/asset/img/cars/hatchback.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"SUV"}',

                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'minute'        => 0,
                'distance'      => '1',
                'calculator'    => 'DISTANCE',
                'image'         => 'public/asset/img/cars/suv.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'name'          => '{"en":"Limousine"}',

                'fixed'         => 20,
                'price'         => 10,
                'status'        => 1,
                'minute'        => 0,
                'distance'      => '1',
                'calculator'    => 'DISTANCE',
                'image'         => 'public/asset/img/cars/limo.png',
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
        ]);
    }
}
