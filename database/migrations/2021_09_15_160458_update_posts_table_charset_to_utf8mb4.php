<?php

use Illuminate\Database\Migrations\Migration;

class UpdatePostsTableCharsetToUtf8mb4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            DB::unprepared('ALTER TABLE `posts` CONVERT TO CHARACTER SET utf8mb4');
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
            DB::unprepared('ALTER TABLE `posts` CONVERT TO CHARACTER SET utf8');
        }
    }
}
