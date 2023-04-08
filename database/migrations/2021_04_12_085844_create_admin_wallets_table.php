<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_wallets', function (Blueprint $table) {
            $table->id();
            $table->integer('transaction_id');
            $table->string('transaction_alias')->nullable();
            $table->string('transaction_desc')->nullable();
            $table->integer('transaction_type')->nullable()->comment('1-commission,2-userrecharge,3-tripdebit,4-providerrecharge,5-providersettle,6-fleetrecharge,7-fleetcommission,8-fleetsettle,9-taxcredit,10-discountdebit,11-discountrecharge,12-userreferral,13-providerreferral,14-peakcommission,15-waitingcommission,16-userdispute,17-providerdispute');
            $table->enum('type', [
                    'C',
                    'D',
                ]);
            $table->double('amount', 15, 8)->default(0);
            $table->double('open_balance', 15, 8)->default(0);
            $table->double('close_balance', 15, 8)->default(0);
            $table->enum('payment_mode', [
                    'CARD',
                    'PAYPAL',
                    'PAYUMONEY',
                    'PAYTM'
                ]);

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
        Schema::dropIfExists('admin_wallets');
    }
}
