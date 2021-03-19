<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCattrsToShareables extends Migration
{
    public function up()
    {
        Schema::table('shareables', function (Blueprint $table) {
            $table->longtext('meta')->after('shareable_id')->nullable()->comment('JSON-encoded meta attributes');
            $table->longtext('cattrs')->after('shareable_id')->nullable()->comment('JSON-encoded custom attributes');
        });
    }

    public function down()
    {
        Schema::table('shareables', function (Blueprint $table) {
            $table->dropColumn(['cattrs','meta']);
        });
    }
}
