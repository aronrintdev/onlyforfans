<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeResourceIdToUuidOnSegpayCalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('segpay_calls', function (Blueprint $table) {
            $table->uuid('resource_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('segpay_calls', function (Blueprint $table) {
            $table->bigIncrements('resource_id')->nullable()->change();
        });
    }
}
