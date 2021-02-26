<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->uuid('user_id')->after('id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('is_creator')->default(false)->after('user_id');
            $table->json('custom')->after('is_creator');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->id('id')->change();
            $table->dropForeign('user_settings_user_id_foreign');
            $table->dropColumn('user_id');
            $table->dropColumn('is_creator');
            $table->dropColumn('custom');
        });
    }
}
