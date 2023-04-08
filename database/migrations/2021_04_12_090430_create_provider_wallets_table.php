<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->integer('transaction_id');
            $table->string('transaction_alias')->nullable();
            $table->string('transaction_desc')->nullable();  
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

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');
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
        Schema::dropIfExists('provider_wallets');
    }
}
