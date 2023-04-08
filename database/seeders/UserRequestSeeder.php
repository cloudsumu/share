<?php

namespace Database\Seeders;

use App\Models\UserRequest;
use Illuminate\Database\Seeder;

class UserRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserRequest::factory()
            ->count(10)
            ->create();
    }
}
