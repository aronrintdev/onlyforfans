<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccessLevelToSharelables extends Migration
{
    public function up()
    {
        Schema::table('shareables', function (Blueprint $table) {
            $table->string('access_level',63)->default('default')->after('shareable_id')->comment('Access Level: Enumeration');
            $table->boolean('is_approved')->default(true)->after('shareable_id');
        });
    }

    public function down()
    {
        Schema::table('shareables', function (Blueprint $table) {
            $table->dropColumn(['access_level', 'is_approved']);
        });
    }
}
