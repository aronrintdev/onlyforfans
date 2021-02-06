<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_tips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tip_to')->unsigned();
            $table->integer('tip_from')->unsigned();
            $table->double('amount')->nullable();            
            $table->timestamps();
            
            $table->foreign('tip_to')->references('id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('tip_from')->references('id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_tips');
    }
}
