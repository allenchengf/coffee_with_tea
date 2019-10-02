<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNetworkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('networks', function (Blueprint $table) {
            $table->unsignedInteger('scheme_id')->nullable()->change();
            $table->dropForeign('networks_scheme_id_foreign');
            $table->foreign('scheme_id')->references('id')->on('schemes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('networks', function (Blueprint $table) {
            $table->unsignedInteger('scheme_id')->change();
            $table->dropForeign('networks_scheme_id_foreign');
            $table->foreign('scheme_id')->references('id')->on('schemes')->onDelete('cascade');
        });
    }
}
