<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasAllowedNsfwToUserSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->boolean('has_allowed_nsfw')->default(false)->after('is_creator');
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
            $table->dropColumn('has_allowed_nsfw');
        });
    }
}
