<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCharsetInCampaignsTable extends Migration
{
    public function up()
    {
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            DB::unprepared('ALTER TABLE `campaigns` CHARACTER SET = utf8mb4, COLLATE = utf8mb4_unicode_ci');
            DB::unprepared('ALTER TABLE `campaigns` CHANGE COLUMN `message` `message` TEXT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci` NOT NULL');
        }
    }

    public function down()
    {
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            DB::unprepared('ALTER TABLE `campaigns` CHARACTER SET = utf8, COLLATE = utf8_unicode_ci');
            DB::unprepared('ALTER TABLE `campaigns` CHANGE COLUMN `message` `message` TEXT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci` NOT NULL');
        }
    }
}
