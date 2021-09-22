<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInitialPeriodToSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->bigInteger('initial_price')->after('manual_charge')->nullable();
            $table->integer('initial_period_interval')->after('manual_charge')->nullable();
            $table->string('initial_period')->after('manual_charge')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['initial_period', 'initial_period_interval', 'initial_price' ]);
        });
    }
}
