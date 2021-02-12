<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFanledgersTable extends Migration
{
    public function up()
    {
        Schema::create('fanledgers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('guid')->nullable();

            $table->uuid('from_account')->comment('The account this transaction is from.');
            $table->uuid('to_account')->comment('The account this transaction goes to.');

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

    public function down()
    {
        Schema::dropIfExists('fanledgers');
    }
}
