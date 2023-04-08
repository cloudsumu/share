<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ServiceTypeSeeder::class);
        \App\Models\Admin::factory()
            ->count(1)
            ->create([
                'name'        => 'Admin Admin',
                'email'       => 'admin@dragon.com',
                'language'    => 'en',
                'password'    => Hash::make('password'),
            ]);

        \App\Models\User::factory()
        ->count(1)
        ->create([
            'language'    => 'en',
            'first_name'  => 'User',
            'last_name'   => 'Dragon',
            'email'       => 'user@dragon.com',
            'password'    => Hash::make('password'),
        ]);

        \App\Models\Provider::factory()
        ->count(1)
        ->create([
            'first_name'  => 'User',
            'last_name'   => 'Provider',
            'email'       => 'partner@dragon.com',
            'status'      => 'approved',
            'password'    => Hash::make('password'),
        ]);

        DB::table('provider_services')->insert([[
            'provider_id'     => 1,
            'service_type_id' => 1,
            'status'          => 'active',
            'service_number'  => 'jss-0987',
            'service_model'   => 'Siena Fire',
        ]]);

        \App\Models\Agent::factory()
            ->count(1)
            ->create([
                'name'     => 'User Agent',
                'email'    => 'agent@dragon.com',
                'password' => Hash::make('password'),
            ]);

        \App\Models\Setting::create([
            'key'   => 'demo_mode',
            'value' => '1',
        ]);

        $this->call(GeoFencingSeeder::class);

        \App\Models\ServiceTypeGeoFencing::create([
            'geo_fencing_id'   => 1,
            'service_type_id'  => 1,
            'fixed'            => 20,
            'price'            => 10,
            'status'           => 1,
            'minute'           => 0,
            'hour'             => '0',
            'city_limits'      => 100,
            'distance'         => '1',
        ]);

        \App\Models\ServiceTypeGeoFencing::create([
            'geo_fencing_id'   => 1,
            'service_type_id'  => 2,
            'fixed'            => 40,
            'price'            => 40,
            'status'           => 1,
            'minute'           => 0,
            'hour'             => '0',
            'city_limits'      => 100,
            'distance'         => '1',
        ]);

        \App\Models\ServiceTypeGeoFencing::create([
            'geo_fencing_id'   => 1,
            'service_type_id'  => 3,
            'fixed'            => 60,
            'price'            => 60,
            'status'           => 1,
            'minute'           => 0,
            'hour'             => '0',
            'city_limits'      => 100,
            'distance'         => '1',
        ]);

        \App\Models\ServiceTypeGeoFencing::create([
            'geo_fencing_id'   => 1,
            'service_type_id'  => 4,
            'fixed'            => 80,
            'price'            => 80,
            'status'           => 1,
            'minute'           => 0,
            'hour'             => '0',
            'city_limits'      => 100,
            'distance'         => '1',
        ]);

        //$this->call(UserSeeder::class);
        //$this->call(ProviderSeeder::class);
        // $this->call(AgentSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(DisputesTableSeeder::class);
        // $this->call(ExtraSeeder::class);
        $this->call(DocumentSeeder::class);
        // $this->call(PromocodeSeeder::class);
        $this->call(CancelReasonSeeder::class);
        // $this->call(UserRequestPaymentSeeder::class);
    }
}
