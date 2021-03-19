<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWatermarkSettingsIntoUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->integer('watermark_file_id')->nullable();
            $table->string('watermark_font_color')->nullable();
            $table->integer('watermark_font_size')->nullable();
            $table->string('watermark_position')->nullable();
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
            $table->dropColumn('watermark_file_id');
            $table->dropColumn('watermark_font_color');
            $table->dropColumn('watermark_font_size');
            $table->dropColumn('watermark_position');
        });
    }
}
