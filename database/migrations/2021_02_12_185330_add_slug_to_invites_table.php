<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugToInvitesTable extends Migration
{
    public function up()
    {
        Schema::table('invites', function (Blueprint $table) {
            // need dummy default value to satisfy sqlite error, see:
            // https://laracasts.com/discuss/channels/general-discussion/migrations-sqlite-general-error-1-cannot-add-a-not-null-column-with-default-value-null
            $table->string('slug')->default('dummy')->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
}
