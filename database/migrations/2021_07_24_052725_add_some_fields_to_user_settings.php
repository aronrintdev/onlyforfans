<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsToUserSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('body_type')->nullable()->after('is_creator');
            $table->string('chest')->nullable()->after('is_creator');
            $table->string('waist')->nullable()->after('is_creator');
            $table->string('hips')->nullable()->after('is_creator');
            $table->string('arms')->nullable()->after('is_creator');
            $table->string('hair_color')->nullable()->after('is_creator');
            $table->string('eye_color')->nullable()->after('is_creator');
            $table->string('age')->nullable()->after('is_creator');
            $table->string('height')->nullable()->after('is_creator');
            $table->string('weight')->nullable()->after('is_creator');
            $table->string('education')->nullable()->after('is_creator');
            $table->string('language')->nullable()->after('is_creator');
            $table->string('ethnicity')->nullable()->after('is_creator');
            $table->string('profession')->nullable()->after('is_creator');
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
            $table->dropColumn([
                'body_type',
                'chest',
                'waist',
                'hips',
                'arms',
                'hair_color',
                'eye_color',
                'age',
                'height',
                'weight',
                'education',
                'language',
                'ethnicity',
                'profession',
            ]);
        });
    }
}
