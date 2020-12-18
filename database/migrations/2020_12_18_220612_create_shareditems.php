<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShareditems extends Migration
{
    public function up()
    {
        Schema::create('shareditems', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->nullable()->comment('User resource being shared with');
            $table->string('sharable_type',255)->nullable()->comment('Polymorhic relation type of resource being shared');
            $table->unsignedInteger('sharable_id')->nullable()->comment('Polymorphic relation FKID of resource being shared');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shareditems');
    }
}
