<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJsonColumnInterchangesStationsToOpruutResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opruut_results', function (Blueprint $table) {
            $table->json('interchanges_stations')->after('interchanges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opruut_results', function (Blueprint $table) {
            $table->dropColumn('interchanges_stations');
        });
    }
}
