<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMycontactsTable extends Migration
{
    public function up()
    {
        Schema::create('mycontacts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('owner_id')->comment('The user who owns the contact');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->uuid('contact_id')->comment('The user who is the contact itself');
            $table->foreign('contact_id')->references('id')->on('users');
            $table->string('alias')->nullable()->comment('Optional nickname the owner can provide the contact');
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mycontacts');
    }
}
