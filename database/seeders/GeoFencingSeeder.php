<?php

namespace Database\Seeders;

use App\Models\GeoFencing;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeoFencingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GeoFencing::factory()
            ->count(10)
            ->create();
    }
}
