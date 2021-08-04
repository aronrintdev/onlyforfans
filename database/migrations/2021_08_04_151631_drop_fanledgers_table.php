<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFanledgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('fanledgers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('fanledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('guid')->nullable();

            // %FIXME: these probably should NOT be nullable, but needed as a workaround for now 
            // until account seeders are written
            // see: https://trello.com/c/LzTUmPCp
            $table->uuid('from_account')->nullable()->comment('The account this transaction is from.');
            $table->uuid('to_account')->nullable()->comment('The account this transaction goes to.');

            $table->string('fltype', 63)->comment('Fan Ledger Type: Enumeration');

            $table->uuid('purchaser_id')->comment('User who sends payment');
            $table->foreign('purchaser_id')->references('id')->on('users');
            $table->uuid('seller_id')->comment('User who receives payment');
            $table->foreign('seller_id')->references('id')->on('users');

            // resource being purchased
            $table->uuidMorphs('purchaseable');

            $table->unsignedInteger('qty');
            // $table->unsignedInteger('quantity');

            $table->unsignedInteger('base_unit_cost_in_cents');
            // $table->unsignedInteger('amount');
            // $table->string('currency')->default('USD');

            $table->unsignedInteger('taxes_in_cents')->default(0);
            $table->unsignedInteger('fees_in_cents')->default(0);
            $table->unsignedInteger('total_amount');
            $table->boolean('is_credit')->default(0)->comment('Is credit or debit (default)');

            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
            $table->softDeletes();
        });
    }
}
