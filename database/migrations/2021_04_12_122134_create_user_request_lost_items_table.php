<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestLostItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_request_lost_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->integer('parent_id')->nullable();
            $table->unsignedBigInteger('user_id');            
            $table->string('lost_item_name');
            $table->string('comments')->nullable();
            $table->enum('comments_by', ['user', 'admin']);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->tinyInteger('is_admin')->default(0);
            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests');
                
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
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
        Schema::dropIfExists('user_request_lost_items');
    }
}
