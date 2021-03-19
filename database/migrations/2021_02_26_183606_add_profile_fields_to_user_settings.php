<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToUserSettings extends Migration
{
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('is_creator')->comment('JSON-encoded meta attributes');
            $table->json('cattrs')->nullable()->after('is_creator')->comment('JSON-encoded custom attributes');
            $table->longtext('about')->nullable()->after('is_creator');
            $table->date('birthdate')->nullable()->after('is_creator');
            $table->string('city')->nullable()->after('is_creator');
            $table->string('country')->nullable()->after('is_creator');
            $table->string('gender', 63)->nullable()->after('is_creator')->comment('Gender Type: Enumeration');
        });
    }

    public function down()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'country',
                'city',
                'birthdate',
                'about',
                'cattrs',
                'meta',
            ]);
        });
    }
}
