<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('domain_id')->unsigned();
            $table->integer('cdn_provider_id')->unsigned();
            $table->integer('dns_provider_id')->default(0);
            $table->string('cname');
            $table->uuid('edited_by')->nullable();
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique([
                'domain_id',
                'cname',
            ], 'cdn');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            $table->foreign('cdn_provider_id')->references('id')->on('cdn_providers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cdns');
    }
}
