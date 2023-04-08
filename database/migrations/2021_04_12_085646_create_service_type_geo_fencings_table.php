<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceTypeGeoFencingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_type_geo_fencings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('geo_fencing_id');
            $table->unsignedBigInteger('service_type_id');
            $table->double('fixed', 10, 2)->nullable();
            $table->double('price', 10, 2)->nullable();
            $table->double('minute', 10, 2)->nullable();
            $table->string('hour')->nullable();
            $table->double('distance', 10, 2)->nullable();
            $table->double('non_geo_price', 10, 2)->default(0);
            $table->double('base_distance')->default(0);
            $table->double('distance_price')->default(0);
            $table->double('city_limits', 10, 2)->default(0);
            $table->double('minute_price')->default(0);
            $table->enum('status', ['1', '0'])->default('1');

            // $table->foreign('service_type_id')
            //     ->references('id')
            //     ->on('service_types');

            // $table->foreign('geo_fencing_id')
            //     ->references('id')
            //     ->on('geo_fencings');

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
        Schema::dropIfExists('service_type_geo_fencings');
    }
}
