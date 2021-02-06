<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPrimaryFieldToVaults extends Migration
{
    public function up()
    {
        Schema::table('vaults', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('vname');
        });
    }

    public function down()
    {
        Schema::table('vaults', function (Blueprint $table) {
            $table->dropColumn(['is_primary']);
        });
    }
}
