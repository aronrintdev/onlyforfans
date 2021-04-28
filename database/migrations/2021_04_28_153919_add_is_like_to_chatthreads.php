<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLikeToChatthreads extends Migration
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
            $table->boolean('is_like')->default(false);
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
            $table->dropColumn(['is_like']);
        });
    }
}
