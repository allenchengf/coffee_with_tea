<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_group_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('domain_id')->unsigned();
            $table->integer('domain_group_id')->unsigned();
            $table->timestamps();
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade')->comment('domains.id');
            $table->foreign('domain_group_id')->references('id')->on('domain_groups')->onDelete('cascade')->comment('domain_groups.id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domain_group_mapping');
    }
}
