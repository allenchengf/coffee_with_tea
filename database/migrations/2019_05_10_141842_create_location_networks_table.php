<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_networks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('continent_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('location');
            $table->unsignedInteger('network_id')->nullable();
            $table->string('edited_by');
            $table->timestamps();
            $table->foreign('continent_id')->references('id')->on('continents');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('network_id')->references('id')->on('networks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_networks');
    }
}
