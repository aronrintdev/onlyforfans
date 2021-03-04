<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsstatesTable extends Migration
{
    public function up()
    {
        Schema::create('usstates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('state_name',255);
            $table->string('state_code',7);
            $table->string('country',7);
            $table->string('stype',255)->nullable();
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usstates');
    }
}
