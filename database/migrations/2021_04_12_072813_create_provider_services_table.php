<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('service_type_id');
            $table->enum('status', ['active', 'offline','riding','hold','balance']);
            $table->string('service_number')->nullable();
            $table->string('service_model')->nullable();

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');

            $table->foreign('service_type_id')
                ->references('id')
                ->on('service_types');
            
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
        Schema::dropIfExists('provider_services');
    }
}
