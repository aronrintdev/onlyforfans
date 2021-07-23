<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSwipeUpLinkToStories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->string('swipe_up_link')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn('swipe_up_link');
        });
    }
}
