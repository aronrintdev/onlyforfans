<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adding system and hidden_at fields to `financial_accounts` table
 */
class UpdateFinancialAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('accounts', function (Blueprint $table) {
            /**
             * The transaction system this account belongs to.
             */
            $table->string('system')->nullable()->after('id');
            /**
             * ISO 4217 Currency Code
             * https://en.wikipedia.org/wiki/ISO_4217
             */
            $table->string('currency', 3)->nullable()->after('type');
            $table->integer('balance')->nullable()->after('currency');
            $table->timestamp('balance_last_updated_at')->nullable()->after('balance');
            $table->integer('pending')->nullable()->after('balance_last_updated_at');
            $table->timestamp('pending_last_updated_at')->nullable()->after('pending');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('accounts', function (Blueprint $table) {
            if (Schema::connection('financial')->hasColumn('accounts', 'system')) {
                $table->dropColumn('system');
            }
            if (Schema::connection('financial')->hasColumn('accounts', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::connection('financial')->hasColumn('accounts', 'balance')) {
                $table->dropColumn('balance');
            }
            if (Schema::connection('financial')->hasColumn('accounts', 'balance_last_updated_at')) {
                $table->dropColumn('balance_last_updated_at');
            }
            if (Schema::connection('financial')->hasColumn('accounts', 'pending')) {
                $table->dropColumn('pending');
            }
            if (Schema::connection('financial')->hasColumn('accounts', 'pending_last_updated_at')) {
                $table->dropColumn('pending_last_updated_at');
            }
            if (Schema::connection('financial')->hasColumn('accounts', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
}
