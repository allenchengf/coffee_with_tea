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
            $table->string('name');
            $table->string('cname');
            $table->integer('ttl');
            $table->uuid('edited_by')->nullable();
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique([
                'domain_id',
                'cname',
            ], 'cdn');
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
