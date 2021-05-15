<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleDatetimeToChatthreads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatthreads', function (Blueprint $table) {
            //
            $table->integer('schedule_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatthreads', function (Blueprint $table) {
            //
            $table->dropColumn(['schedule_datetime']);
        });
    }
}
