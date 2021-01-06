<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFanledgers extends Migration
{
    public function up()
    {
        Schema::create('fanledgers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid')->unique();

            $table->string('fltype',63)->comment('Fan Ledger Type: Enumeration');
            $table->unsignedInteger('purchaser_id');
            $table->foreign('purchaser_id')->references('id')->on('users');

            $table->string('purchaseable_type',255)->comment('Polymorhic relation type of resource being purchased, eg post, subscription, etc');
            $table->unsignedInteger('purchaseable_id')->comment('Polymorphic relation FKID of resource being purchased');

            $table->unsignedInteger('qty');
            $table->unsignedInteger('base_unit_cost_in_cents');
            $table->unsignedInteger('taxes_in_cents')->default(0);
            $table->unsignedInteger('fees_in_cents')->default(0);
            $table->boolean('is_credit')->default(0)->comment('Is credit or debit (default)');

            $table->longtext('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->longtext('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fanledgers');
    }
}
