<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicePeakHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_peak_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_type_id');
            $table->unsignedBigInteger('peak_hours_id');
            $table->float('min_price', 10, 2)->default(0);
            
            $table->foreign('service_type_id')
                ->references('id')
                ->on('service_types');
                
            $table->foreign('peak_hours_id')
                ->references('id')
                ->on('peak_hours');
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
        Schema::dropIfExists('service_peak_hours');
    }
}
