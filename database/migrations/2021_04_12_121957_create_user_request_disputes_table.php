<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_request_disputes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->enum('dispute_type', ['user', 'provider']);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('provider_id');
            $table->string('dispute_name');
            $table->string('dispute_title')->nullable();
            $table->string('comments')->nullable();
            $table->float('refund_amount',10,2)->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->tinyInteger('is_admin')->default(0);
            
            $table->foreign('request_id')
                ->references('id')
                ->on('user_requests');
                
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
                
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
        Schema::dropIfExists('user_request_disputes');
    }
}
