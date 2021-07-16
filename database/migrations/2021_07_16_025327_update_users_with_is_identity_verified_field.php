<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersWithIsIdentityVerifiedField extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('verifyrequest_id')->nullable()->after('username')->comment("FK - latest [verifyrequest] record if any");
            //$table->foreign('verifyrequest_id')->references('id')->on('verifyrequests');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([ 'verifyrequest_id' ]);
        });
    }
}
