<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('company')->nullable();
            $table->string('mobile');
            $table->double('commission', 5, 2)->default(0);
            $table->double('wallet_balance', 10, 2)->default(0);
            $table->string('stripe_cust_id')->nullable();
            $table->string('language')->nullable()->default('en');
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();
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
        Schema::dropIfExists('agents');
    }
}