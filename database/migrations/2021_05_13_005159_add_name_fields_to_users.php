<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameFieldsToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('lastname')->nullable()->after('username')->comment('Serves as user display/stage name');
            $table->string('firstname')->nullable()->after('username')->comment('Serves as user display/stage name');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'firstname',
                'lastname',
            ]);
        });
    }
}
