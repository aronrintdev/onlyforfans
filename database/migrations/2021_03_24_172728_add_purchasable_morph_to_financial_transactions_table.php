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
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->dropColumn('shareable_id');
        });

        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->uuid('purchasable_id')->nullable()->after('reference_id');
            $table->string('purchasable_type')->nullable()->after('reference_id');
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
            $table->dropColumn(['purchasable_id', 'purchasable_type']);
        });

        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->uuid('shareable_id')->nullable();
        });
    }
}
