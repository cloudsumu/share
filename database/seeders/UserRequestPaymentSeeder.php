<?php

namespace Database\Seeders;

use App\Models\UserRequest;
use Illuminate\Database\Seeder;
use App\Models\UserRequestPayment;

class UserRequestPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=0; $i<=50; $i++) {
            $user_request = UserRequest::factory()->create();
            if($user_request->status == 'COMPLETED') {
                UserRequestPayment::factory()
                    ->create([
                        'request_id' => $user_request->id,
                        'user_id' => $user_request->user_id,
                        'provider_id' => $user_request->provider_id,
                    ]);
            }
            else {
                // 
            }
        }
    }
}
