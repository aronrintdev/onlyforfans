<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Fan Accounts Table should store the financial account information for a owner of the account.
         */
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            /**
             * The entity that owns the account. This will usually be a user, but may be a group, the site owner, or a
             *   taxes collection account
             */
            $table->uuidMorphs('owner');
            /**
             * Name of the account
             */
            $table->string('name');

            /**
             * Type of the account. user, user currency exchange, card, business, ect.
             */
            $table->string('type')->comment('What type of account this is');
            /**
             * Resource Attached to this account, such as stripe account/credit card, ACH account, or other business
             *   account. This is used for money in and out from sources we don't have direct control over.
             */
            $table->nullableUuidMorphs('resource');
            /**
             * If the account if verified, most of the time for stuff like stripe verification.
             */
            $table->boolean('verified')->default(false);
            /**
             * If the account can make transactions, allows a way for admins to block an account from making further
             *   transactions.
             */
            $table->boolean('can_make_transactions')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fan_accounts');
    }
}
