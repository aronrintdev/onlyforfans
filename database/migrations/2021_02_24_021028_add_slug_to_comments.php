<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToComments extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
