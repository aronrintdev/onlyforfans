<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePurchasableToResourceOnFinancialTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->renameColumn('purchasable_type', 'resource_type');
        });
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->renameColumn('purchasable_id', 'resource_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->renameColumn( 'resource_type', 'purchasable_type');
        });
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->renameColumn('resource_id', 'purchasable_id');
        });
    }
}
