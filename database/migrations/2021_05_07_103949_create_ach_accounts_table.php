<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAchAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->create('ach_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /** User that created this ach account */
            $table->uuid('user_id');

            $table->enum('type', ['individual', 'company']);

            /** Name on Account */
            $table->string('name');

            /**
             * ISO 3166-2
             * https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes
             */
            $table->string('residence_country', 2)->default('US');

            $table->string('beneficiary_name');

            /** Name of bank */
            $table->string('bank_name');

            /** Bank Routing Number */
            $table->string('routing_number');

            /** Bank Account Number */
            $table->string('account_number');

            /** Bank Account Type */
            $table->enum('account_type', [ 'CHK', 'SAV' ])->default('CHK');

            /**
             * ISO 3166-2
             * https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes
             */
            $table->string('bank_country', 2)->default('US');

            /**
             * ISO 4217
             * https://en.wikipedia.org/wiki/ISO_4217
             */
            $table->string('currency', 3)->default('USD');

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->dropIfExists('ach_accounts');
    }
}
