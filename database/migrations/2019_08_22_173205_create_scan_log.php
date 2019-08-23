<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScanLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cdn_provider_id')->unsigned();
            $table->integer('scan_platform_id')->unsigned();
            $table->integer('location_network_id')->unsigned();
            $table->string('latency', 10)->nullable();
            $table->foreign('cdn_provider_id')->references('id')->on('cdn_providers')->onDelete('cascade');
            $table->foreign('scan_platform_id')->references('id')->on('scan_platforms')->onDelete('cascade');
            $table->foreign('location_network_id')->references('id')->on('location_networks')->onDelete('cascade');
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
        Schema::dropIfExists('scan_logs');
    }
}
