<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCharsetInCommentsTable extends Migration
{
    public function up()
    {
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            DB::unprepared('ALTER TABLE `comments` CHARACTER SET = utf8mb4, COLLATE = utf8mb4_unicode_ci');
            DB::unprepared('ALTER TABLE `comments` CHANGE COLUMN `description` `description` TEXT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci` NOT NULL');
        }
    }

    public function down()
    {
        
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'description')) {
                $table->text('description')->nullable();
            }
            if (Schema::hasColumn('comments', 'message')) {
                $table->dropColumn('message');
            }
        });
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            DB::unprepared('ALTER TABLE `comments` CHARACTER SET = utf8, COLLATE = utf8_unicode_ci');
            DB::unprepared('ALTER TABLE `comments` CHANGE COLUMN `description` `description` TEXT CHARACTER SET `utf8` COLLATE `utf8_unicode_ci` NOT NULL');
        }
    }
}
