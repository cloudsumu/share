<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('provider_id');
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');

        });

        Schema::create('request_current_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_filters');
        Schema::dropIfExists('request_current_providers');
    }
}
