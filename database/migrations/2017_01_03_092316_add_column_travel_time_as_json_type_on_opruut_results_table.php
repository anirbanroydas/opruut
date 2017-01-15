<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTravelTimeAsJsonTypeOnOpruutResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opruut_results', function (Blueprint $table) {
            $table->json('travel_time')->after('travel_distance');
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
            $table->dropColumn('travel_time');
        });
    }
}
