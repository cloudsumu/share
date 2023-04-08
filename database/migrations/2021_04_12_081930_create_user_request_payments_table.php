<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_request_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('promocode_id')->nullable();            
            $table->string('payment_id')->nullable();

            $table->foreign('promocode_id')
                ->references('id')
                ->on('promocodes');

            $table->foreign('agent_id')
                ->references('id')
                ->on('agents');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests');

            $table->string('payment_mode')->nullable();
            $table->float('fixed', 10, 2)->default(0);
            $table->float('distance',10, 2)->default(0);
            $table->float('minute',10,2)->default(0);
            $table->integer('geo_fencing_minute')->default(0);
            $table->integer('non_geo_fencing_minute')->default(0);
            $table->float('hour',10,2)->default(0);
            $table->float('peak_price', 10, 2)->default(0);
            $table->float('driver_beta', 10, 2)->default(0);
            $table->float('commision',  10, 2)->default(0); // Admin Commission
            $table->float('commision_per',  5, 2)->default(0); // Admin Commission Percentage
            $table->float('agent',  10, 2)->default(0);
            $table->float('agent_per',  5, 2)->default(0); // Agent Commission Percentage
            $table->float('discount',   10, 2)->default(0);
            $table->float('discount_per',   5, 2)->default(0);
            $table->float('tax',        10, 2)->default(0);
            $table->float('tax_per',        5, 2)->default(0);
            $table->float('wallet',     10, 2)->default(0);
            $table->tinyInteger('is_partial')->comment('0-No,1-Yes')->default(0);
            $table->float('cash',     10, 2)->default(0);
            $table->float('card',     10, 2)->default(0);
            $table->float('online',     10, 2)->default(0);
            $table->float('surge',      10, 2)->default(0);
            $table->double('geo_fencing_total', 10, 2)->default(0);
            $table->double('none_geo_fencing_total', 10, 2)->default(0);
            $table->float('toll_charge',  10, 2)->default(0);
            $table->float('round_of',  10, 2)->default(0);
            $table->float('peak_amount', 10, 2)->default(0);
            $table->float('peak_comm_amount', 10, 2)->default(0);
            $table->integer('total_waiting_time')->default(0);
            $table->float('waiting_amount', 10, 2)->default(0);
            $table->float('waiting_comm_amount', 10, 2)->default(0);
            $table->float('tips',10, 2)->default(0);
            $table->float('total',10, 2)->default(0);
            $table->float('payable',8,2)->default(0);
            $table->double('night_fare', 8, 2)->nullable()->default(0.00);
            $table->double('percentage', 8, 2)->nullable()->default(0.00);
            $table->float('provider_commission',8,2)->default(0);
            $table->float('provider_pay',8,2)->default(0);
            $table->double('return_travel_fare', 10, 2)->default(0);
            $table->double('eta_discount', 8, 2)->default(0);
            $table->double('non_geo_price', 10, 2)->default(0);
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
        Schema::dropIfExists('user_request_payments');
    }
}
