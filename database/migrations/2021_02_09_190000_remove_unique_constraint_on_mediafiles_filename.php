<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueConstraintOnMediafilesFilename extends Migration
{
    public function up()
    {
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->dropUnique('mediafiles_filename_unique');
        });
    }

    public function down()
    {
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->unique('filename');
        });
    }
}
