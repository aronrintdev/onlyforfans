<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBookmarksToDeprecate extends Migration
{
    public function up()
    {
        Schema::rename('bookmarks', 'deprecated_bookmarks');
    }

    public function down()
    {
        Schema::rename('deprecated_bookmarks', 'bookmarks');
    }
}
