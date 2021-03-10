<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFinancialTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->renameColumn('account', 'account_id');
            /**
             * Changing to ISO_4217 Currency code length
             * https://en.wikipedia.org/wiki/ISO_4217
             */
            $table->string('currency', 3)->change();
            $table->renameColumn('reference', 'reference_id');

            // Show we know what type of transaction this was, i.e. sale, chargeback, refund
            $table->string('type')->nullable()->after('currency');

            // Moving resource reference to access table
            if (Schema::hasColumn('financial_transactions', 'resource_type')) {
                $table->dropColumn('resource_type');
            }
            if (Schema::hasColumn('financial_transactions', 'resource_id')) {
                $table->dropColumn('resource_id');
            }
        });
        Schema::table('financial_transactions',function (Blueprint $table) {
            // Reference to access table
            $table->uuid('access_id')->nullable()->after('reference_id');
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
            if (Schema::hasColumn('financial_transactions', 'account_id')) {
                $table->renameColumn('account_id', 'account');
            }
            $table->string('currency')->change();

            if (Schema::hasColumn('financial_transactions', 'reference_id')) {
                $table->renameColumn('reference_id', 'reference');
            }

            if (Schema::hasColumn('financial_transactions', 'type')) {
                $table->dropColumn('type');
            }

            if (!Schema::hasColumn('financial_transactions', 'resource_type')) {
                $table->nullableUuidMorphs('resource');
            }

            if (Schema::hasColumn('financial_transactions', 'access_id')) {
                $table->dropColumn('access_id');
            }
        });
    }
}
