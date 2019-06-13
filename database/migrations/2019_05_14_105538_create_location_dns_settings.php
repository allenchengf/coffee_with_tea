<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationDnsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_dns_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_record_id')->default(0)->comment('Third Party Dns Provider Record Id');
            $table->integer('location_networks_id')->unsigned();
            $table->integer('cdn_id')->unsigned();
            $table->integer('domain_id')->unsigned();
            $table->uuid('edited_by')->nullable()->comment('設定者');
            $table->timestamps();
            $table->foreign('location_networks_id')->references('id')->on('location_networks')->comment('location_networks.id');
            $table->foreign('cdn_id')->references('id')->on('cdns')->comment('cdns.id');
            $table->foreign('domain_id')->references('id')->on('domains')->comment('domains.id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_dns_settings');
    }
}
