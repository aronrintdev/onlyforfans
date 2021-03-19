<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToTimelines extends Migration
{
    public function up()
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
