<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpruutResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opruut_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('opruut_request_id')->unsigned();
            $table->json('stations');
            $table->json('routes');
            $table->integer('station_count');
            $table->integer('interchanges')->unsigned();
            $table->float('travel_time');
            $table->float('time_factor');
            $table->float('comfort_factor');
            $table->timestamps();

            $table->foreign('opruut_request_id')
                  ->references('id')->on('opruut_requests')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opruut_results');
    }
}
