<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnToLocationNetworkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_networks', function (Blueprint $table) {
            $table->boolean('status')->default(false)->comment('1:開 ; 0:關')->after('mapping_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_networks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
