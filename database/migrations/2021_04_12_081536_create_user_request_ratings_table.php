<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_request_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('provider_id');
            $table->integer('user_rating')->default(0);
            $table->integer('provider_rating')->default(0);
            $table->string('user_comment')->nullable();
            $table->string('provider_comment')->nullable();
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');

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
        Schema::dropIfExists('user_request_ratings');
    }
}
