<?php

use App\Models\Financial\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveFinancialTablesToNewDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $financialDB = Config::get('database.connections.financial.database');
        $financialPrefix = Config::get('database.connections.financial.prefix');
        $defaultConnection = Config::get('database.default');
        $db = Config::get("database.connections.$defaultConnection.database");

        // Create and migrate data if it needs to be
        $this->updateTable($defaultConnection, $db, '', 'financial_accounts',                       'financial', $financialDB, $financialPrefix, 'accounts');
        $this->updateTable($defaultConnection, $db, '', 'financial_currency_exchange_transactions', 'financial', $financialDB, $financialPrefix, 'currency_exchange_transactions');
        $this->updateTable($defaultConnection, $db, '', 'financial_flags',                          'financial', $financialDB, $financialPrefix, 'flags');
        $this->updateTable($defaultConnection, $db, '', 'financial_system_owners',                  'financial', $financialDB, $financialPrefix, 'system_owners');
        $this->updateTable($defaultConnection, $db, '', 'financial_transaction_summaries',          'financial', $financialDB, $financialPrefix, 'transaction_summaries');
        $this->updateTable($defaultConnection, $db, '', 'financial_transactions',                   'financial', $financialDB, $financialPrefix, 'transactions');
        $this->updateTable($defaultConnection, $db, '', 'segpay_calls',                             'financial', $financialDB, $financialPrefix, 'segpay_calls');
        $this->updateTable($defaultConnection, $db, '', 'segpay_cards',                             'financial', $financialDB, $financialPrefix, 'segpay_cards');
    }

    private function updateTable($oldConnection, $oldDB, $oldPrefix, $oldName, $newConnection, $newDB, $newPrefix, $newName)
    {
        if (Schema::connection($oldConnection)->hasTable($oldName)) {
            $oldTable = $oldPrefix . $oldName;
            $newTable = $newPrefix . $newName;
            // If these are the same then their is no data to migrate
            if ("$newDB.$newTable" == "$oldDB.$oldTable") {
                return;
            }
            // dump('test', "$newDB.$newTable", "$oldDB.$oldTable", "$newDB.$newTable" == "$oldDB.$oldTable");
            if (!Schema::connection($newConnection)->hasTable($newTable)) {
                if (Config::get("database.connections.$oldConnection.driver") == 'sqlite') {
                    if ($newDB === ':memory:') {
                        DB::statement("create table `$newTable` as select * from `$oldTable`");
                    } else {
                        DB::statement("create table `$newDB`.`$newTable` as select * from `$oldDB`.`$oldTable`");
                    }
                } else {
                    DB::statement("create table `$newDB`.`$newTable` select * from `$oldDB`.`$oldTable`");
                }
            } else {
                DB::statement("insert `$newDB`.`$newTable` select * from `$oldDB`.`$oldTable`");
            }
            Schema::connection($oldConnection)->dropIfExists($oldName);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $financialDB = Config::get('database.connections.financial.database');
        $financialPrefix = Config::get('database.connections.financial.prefix');
        $defaultConnection = Config::get('database.default');
        $db = Config::get("database.connections.$defaultConnection.database");

        // Create and migrate data if it needs to be
        $this->updateTable('financial', $financialDB, $financialPrefix, 'accounts',                       $defaultConnection, $db, '', 'financial_accounts');
        $this->updateTable('financial', $financialDB, $financialPrefix, 'currency_exchange_transactions', $defaultConnection, $db, '', 'financial_currency_exchange_transactions');
        $this->updateTable('financial', $financialDB, $financialPrefix, 'flags',                          $defaultConnection, $db, '', 'financial_flags');
        $this->updateTable('financial', $financialDB, $financialPrefix, 'system_owners',                  $defaultConnection, $db, '', 'financial_system_owners');
        $this->updateTable('financial', $financialDB, $financialPrefix, 'transaction_summaries',          $defaultConnection, $db, '', 'financial_transaction_summaries');
        $this->updateTable('financial', $financialDB, $financialPrefix, 'transactions',                   $defaultConnection, $db, '', 'financial_transactions');
        $this->updateTable('financial', $financialDB, $financialPrefix, 'segpay_calls',                   $defaultConnection, $db, '', 'segpay_calls');
        $this->updateTable('financial', $financialDB, $financialPrefix, 'segpay_cards',                   $defaultConnection, $db, '', 'segpay_cards');
    }
}
