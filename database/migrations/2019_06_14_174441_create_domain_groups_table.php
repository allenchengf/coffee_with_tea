<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domain_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_group_id')->unsigned();
            $table->string('name');
            $table->string('label')->nullable();
            $table->uuid('edited_by')->nullable()->comment('設定者');
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
        Schema::dropIfExists('domain_groups');        
    }
}
