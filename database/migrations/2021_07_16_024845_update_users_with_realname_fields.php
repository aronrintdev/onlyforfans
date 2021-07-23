<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersWithRealnameFields extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('real_lastname')->nullable()->after('username')->comment('User legal last name');
            $table->string('real_firstname')->nullable()->after('username')->comment('User legal first name');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([ 'real_firstname', 'real_lastname' ]);
        });
    }
}
