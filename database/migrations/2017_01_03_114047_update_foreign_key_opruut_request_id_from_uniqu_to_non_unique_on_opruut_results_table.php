<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateForeignKeyOpruutRequestIdFromUniquToNonUniqueOnOpruutResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opruut_results', function (Blueprint $table) {
            $table->dropForeign('opruut_results_opruut_request_id_foreign');
            $table->dropColumn('opruut_request_id');
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
            $table->integer('opruut_request_id')->unsigned()->unique()->index();

            $table->foreign('opruut_request_id')
                  ->references('id')->on('opruut_requests')
                  ->onDelete('cascade');
        });
    }
}