<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            $driver_name = DB::getDriverName();

            if ($driver_name == 'mysql') {
                $table->dropForeign('networks_scheme_id_foreign');
                $table->foreign('scheme_id')->references('id')->on('schemes')->onDelete('set null');
            }
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
            $driver_name = DB::getDriverName();

            if ($driver_name == 'mysql') {
                $table->dropForeign('networks_scheme_id_foreign');
                $table->foreign('scheme_id')->references('id')->on('schemes')->onDelete('cascade');
            }

        });
    }
}
