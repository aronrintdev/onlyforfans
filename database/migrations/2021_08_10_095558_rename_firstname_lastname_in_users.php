<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameFirstnameLastnameInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('firstname', 'deprecated_firstname');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('lastname', 'deprecated_lastname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('deprecated_firstname', 'firstname');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('deprecated_lastname', 'lastname');
        });
    }
}
