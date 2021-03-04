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
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->string('system')->after('id');
            $table->integer('balance')->nullable()->after('type');
            $table->timestamp('balance_last_updated_at')->nullable()->after('balance');
            $table->integer('pending')->nullable()->after('balance_last_updated_at');
            $table->timestamp('pending_last_updated_at')->nullable()->after('pending');
            $table->timestamp('hidden_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropColumn('system');
            $table->dropColumn('balance');
            $table->dropColumn('balance_last_updated_at');
            $table->dropColumn('pending');
            $table->dropColumn('pending_last_updated_at');
            $table->dropColumn('hidden_at');
        });
    }
}
