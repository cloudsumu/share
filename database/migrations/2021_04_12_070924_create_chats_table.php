<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('provider_id');
            $table->text('message');
            $table->enum('type', ['up', 'pu']);
            $table->boolean('delivered');
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chats');
    }
}
