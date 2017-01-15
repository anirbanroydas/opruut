<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRideTimeTypeFromTimeToTimestampInOpruutRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opruut_requests', function (Blueprint $table) {
            $table->timestamp('ride_time')->useCurrent();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opruut_requests', function (Blueprint $table) {
            $table->dropColumn('ride_time');
        });
    }
}
