<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCharsetInMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            DB::unprepared('ALTER TABLE `chatmessages` CHARACTER SET = utf8mb4, COLLATE = utf8mb4_unicode_ci');
            DB::unprepared('ALTER TABLE `chatmessages` CHANGE COLUMN `mcontent` `mcontent` TEXT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci` NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            DB::unprepared('ALTER TABLE `chatmessages` CHARACTER SET = utf8, COLLATE = utf8_unicode_ci');
            DB::unprepared('ALTER TABLE `chatmessages` CHANGE COLUMN `mcontent` `mcontent` TEXT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci` NOT NULL');
        }
    }
}
