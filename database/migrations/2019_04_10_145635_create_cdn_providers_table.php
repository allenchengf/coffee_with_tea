<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCdnProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cdn_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('status', ['active','stop']);
            $table->integer('user_group_id')->unsigned();
            $table->integer('ttl')->nullable();
            $table->uuid('edited_by')->nullable();
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
        Schema::dropIfExists('cdn_providers');
    }
}
