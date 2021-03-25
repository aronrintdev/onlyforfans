<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchasableMorphToFinancialTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn('shareable_id');
            $table->nullableUuidMorphs('purchasable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->uuid('shareable_id')->nullable();
            $table->dropColumn(['purchasable_id', 'purchasable_type']);
        });
    }
}
