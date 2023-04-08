1<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('email')->unique();
            $table->enum('gender', [
                'MALE',
                'FEMALE',
            ])->default('MALE');
            $table->unsignedBigInteger('geo_fencing_id')->nullable();
            $table->double('latitude', 15, 8)->nullable();
            $table->double('longitude', 15, 8)->nullable();
            $table->string('address')->nullable();
            $table->mediumInteger('otp')->default(0);
            $table->string('language')->nullable()->default('en');
            $table->string('avatar')->nullable();
            $table->string('country_code')->nullable();
            $table->string('mobile');
            $table->string('password');
            $table->enum('blocked', ['1', '0'])->default('0');
            $table->enum('verified', ['1', '0'])->default('0');
            $table->enum('status', ['document', 'card', 'onboarding', 'approved', 'banned', 'balance'])->default('document');
            $table->string('stripe_acc_id')->nullable();
            $table->string('stripe_cust_id')->nullable();
            $table->string('paypal_email')->nullable();
            $table->enum('login_by', ['manual', 'facebook', 'google']);
            $table->string('social_unique_id')->nullable();
            $table->double('wallet_balance', 10, 2)->default(0);
            $table->string('referral_unique_id', 10)->nullable();
            $table->string('qrcode_url')->nullable();
            $table->softDeletes();

            $table->rememberToken();
            $table->timestamps();

            $table
                ->foreign('agent_id')
                ->references('id')
                ->on('agents');

            $table
                ->foreign('geo_fencing_id')
                ->references('id')
                ->on('geo_fencings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers');
    }
}
