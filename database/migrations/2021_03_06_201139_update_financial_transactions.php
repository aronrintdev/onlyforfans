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
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->renameColumn('account', 'account_id');
        });

        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->renameColumn('reference', 'reference_id');
        });

        if (Schema::connection('financial')->hasColumn('transactions', 'resource_type')) {
            Schema::connection('financial')->table('transactions', function (Blueprint $table) {
                $table->dropColumn('resource_type');
            });
        }

        if (Schema::connection('financial')->hasColumn('transactions', 'resource_id')) {
            Schema::connection('financial')->table('transactions', function (Blueprint $table) {
                $table->dropColumn('resource_id');
            });
        }

        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            /**
             * Changing to ISO_4217 Currency code length
             * https://en.wikipedia.org/wiki/ISO_4217
             */
            $table->string('currency', 3)->change();
            // Show we know what type of transaction this was, i.e. sale, chargeback, refund
            $table->string('type')->nullable()->after('currency');
        });

        Schema::connection('financial')->table('transactions',function (Blueprint $table) {
            // Reference to access table
            $table->uuid('shareable_id')->nullable()->after('reference_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::connection('financial')->hasColumn('transactions', 'account_id')) {
            Schema::connection('financial')->table('transactions', function (Blueprint $table) {
                $table->renameColumn('account_id', 'account');
            });
        }
        if (Schema::connection('financial')->hasColumn('transactions', 'reference_id')) {
            Schema::connection('financial')->table('transactions', function (Blueprint $table) {
                $table->renameColumn('reference_id', 'reference');
            });
        }
        if (Schema::connection('financial')->hasColumn('transactions', 'type')) {
            Schema::connection('financial')->table('transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
        if (!Schema::connection('financial')->hasColumn('transactions', 'resource_type')) {
            Schema::connection('financial')->table('transactions', function (Blueprint $table) {
                $table->nullableUuidMorphs('resource');
            });
        }
        if (Schema::connection('financial')->hasColumn('transactions', 'shareable_id')) {
            Schema::connection('financial')->table('transactions', function (Blueprint $table) {
                $table->dropColumn('shareable_id');
            });
        }
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->string('currency')->change();
        });
    }
}
