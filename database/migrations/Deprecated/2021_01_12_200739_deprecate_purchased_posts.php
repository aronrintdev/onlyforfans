<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeprecatePurchasedPosts extends Migration
{
    public function up()
    {
        Schema::table('purchased_posts', function (Blueprint $table) {
            Schema::rename('post_tips', 'deprecated_post_tips');
            Schema::rename('purchased_posts', 'deprecated_purchased_posts');
            Schema::rename('post_shares', 'deprecated_post_shares');
            Schema::rename('subscriptions', 'deprecated_subscriptions');
            Schema::rename('users_tips', 'deprecated_users_tips');
            Schema::rename('followers', 'deprecated_followers');
            Schema::rename('post_follows', 'deprecated_post_follows');
        });
    }

    public function down()
    {
        Schema::table('purchased_posts', function (Blueprint $table) {
            Schema::rename('deprecated_post_follows', 'post_follows');
            Schema::rename('deprecated_followers', 'followers');
            Schema::rename('deprecated_users_tips', 'users_tips');
            Schema::rename('deprecated_subscriptions', 'subscriptions');
            Schema::rename('deprecated_post_shares', 'post_shares');
            Schema::rename('deprecated_purchased_posts', 'purchased_posts');
            Schema::rename('deprecated_post_tips', 'post_tips');
        });
    }
}
