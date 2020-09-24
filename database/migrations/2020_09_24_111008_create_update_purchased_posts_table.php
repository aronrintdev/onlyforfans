<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpdatePurchasedPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchased_posts', function (Blueprint $table) {
            $table->integer('post_id')->unsigned()->change();
                
            $table->foreign('post_id')->references('id')->on('posts')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchased_posts', function (Blueprint $table) {
            $table->dropForeign('purchased_posts_post_id_foreign');
        });
    }
}
