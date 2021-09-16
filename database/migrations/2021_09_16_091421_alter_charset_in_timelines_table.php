<?php

use Illuminate\Database\Migrations\Migration;

class AlterCharsetInTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('ALTER TABLE `timelines` CHARACTER SET = utf8mb4, COLLATE = utf8mb4_unicode_ci');
        DB::unprepared('ALTER TABLE `timelines` CHANGE COLUMN `about` `about` TEXT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci` NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('ALTER TABLE `timelines` CHARACTER SET = utf8, COLLATE = utf8_unicode_ci');
        DB::unprepared('ALTER TABLE `timelines` CHANGE COLUMN `about` `about` TEXT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci` NOT NULL');
    }
}
