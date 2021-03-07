<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSegpayCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segpay_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('owner');
            $table->string('token');
            $table->string('nickname')->nullable();
            $table->string('card_type')->nullable();
            $table->string('last_4')->nullable();
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
        Schema::dropIfExists('segpay_cards');
    }
}
