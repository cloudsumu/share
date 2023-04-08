<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->enum('payment_mode', ['CASH', 'CARD', 'PAYPAL', 'PAYPAL-ADAPTIVE', 'PAYUMONEY', 'PAYTM'])->default('CASH');
            $table->enum('user_type', ['INSTANT', 'NORMAL'])->default('NORMAL');
            $table->enum('gender', [
                'MALE',
                'FEMALE',
            ])->default('MALE');
            $table->string('country_code')->nullable();
            $table->string('mobile')->unique();
            $table->string('device_token')->nullable();
            $table->string('device_id')->nullable();
            $table->enum('device_type', ['web', 'android', 'ios']);
            $table->enum('login_by', ['manual', 'facebook', 'google']);
            // $table->decimal('rating', 4, 2)->default(5);
            $table->string('language')->nullable()->default('en');
            $table->string('qrcode_url')->nullable();
            $table->string('referral_unique_id', 10)->nullable();
            $table->mediumInteger('referal_count')->default(0);
            $table->timestamp('trial_ends_at')->nullable();
            $table->text('avatar')->nullable();
            $table->string('password');
            $table->string('social_unique_id')->nullable();
            $table->double('latitude', 15, 8)->nullable();
            $table->double('longitude', 15, 8)->nullable();
            $table->string('stripe_cust_id')->nullable();
            $table->float('wallet_balance')->default(0);
            $table->mediumInteger('otp')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('blocked', ['1', '0'])->default('0');
            
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
