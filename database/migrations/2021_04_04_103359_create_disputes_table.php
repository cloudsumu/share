<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('dispute_type', ['user', 'provider','agent'])->default('user');
            $table->string('dispute_name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
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
        Schema::dropIfExists('disputes');
    }

    // /**
    //  * Run the migrations.
    //  *
    //  * @return void
    //  */
    // public function up()
    // {
    //     Schema::create('disputes', function (Blueprint $table) {
    //         $table->id();
            
    //         $table->string('name');
    //         $table->string('from', 10); // type of user.
    //         $table->string('user_id');
    //         $table->unsignedBigInteger('dispute_type_id');
    //         $table->longText('description');
    //         $table->longText('response')->nullable();
    //         // 0 means unresolved, 1 means resolved.
    //         $table->enum('status', [0, 1])->default(0);
    //         $table->softDeletes();
    //         $table->timestamps();
    //         $table
    //             ->foreign('dispute_type_id')
    //             ->references('id')
    //             ->on('dispute_types');
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  *
    //  * @return void
    //  */
    // public function down()
    // {
    //     Schema::dropIfExists('disputes');
    // }
}
