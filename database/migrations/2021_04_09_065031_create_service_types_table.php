<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->integer('capacity')->default(0);
            $table->float('fixed', 8, 2)->nullable()->default(0.00);
            $table->float('price', 8, 2)->nullable()->default(0.00);
            $table->float('minute', 8, 2)->nullable()->default(0.00);
            $table->integer('outstation_km')->nullable();
            $table->integer('rental_fare')->nullable();
            $table->integer('night_fare')->nullable();
            $table->integer('roundtrip_km')->nullable();
            $table->float('rental_hour_price', 8, 2)->nullable()->default(0.00);
            $table->float('rental_km_price', 8, 2)->nullable()->default(0.00);
            $table->integer('outstation_driver')->nullable();
            $table->string('hour')->nullable();
            $table->integer('distance')->nullable();
            $table->enum('calculator', ['MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEHOUR']);
            $table->string('description')->nullable();
            $table->integer('waiting_free_mins')->default(0);
            $table->float('waiting_min_charge', 10, 2)->default(0);
            
            $table->softDeletes();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('service_types');
    }
}
