<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameLegacyTables extends Migration
{
    public function up()
    {
        Schema::table('albums', function (Blueprint $table) {
            Schema::rename('deprecated_followers', 'z_deprecated_followers');
            Schema::rename('deprecated_post_follows', 'z_deprecated_post_follows');
            Schema::rename('deprecated_post_shares', 'z_deprecated_post_shares');
            Schema::rename('deprecated_post_tips', 'z_deprecated_post_tips');
            Schema::rename('deprecated_purchased_posts', 'z_deprecated_purchased_posts');
            Schema::rename('deprecated_subscriptions', 'z_deprecated_subscriptions');
            Schema::rename('deprecated_users_tips', 'z_deprecated_users_tips');

            Schema::rename('albums', 'z_deprecated_albums');
            Schema::rename('album_media', 'z_deprecated_album_media');
            Schema::rename('announcement_user', 'z_deprecated_announcement_user');
            Schema::rename('announcements', 'z_deprecated_announcements');
            Schema::rename('bank_account_details', 'z_deprecated_bank_account_details');
            Schema::rename('categories', 'z_deprecated_categories');
            Schema::rename('comment_likes', 'z_deprecated_comment_likes');
            Schema::rename('event_user', 'z_deprecated_event_user');
            Schema::rename('events', 'z_deprecated_events');
            Schema::rename('favourite_users', 'z_deprecated_favourite_users');
            Schema::rename('group_user', 'z_deprecated_group_user');
            Schema::rename('groups', 'z_deprecated_groups');
            Schema::rename('hashtags', 'z_deprecated_hashtags');
            Schema::rename('languages', 'z_deprecated_languages');
            Schema::rename('media', 'z_deprecated_media');
            Schema::rename('page_likes', 'z_deprecated_page_likes');
            Schema::rename('page_user', 'z_deprecated_page_user');
            Schema::rename('pages', 'z_deprecated_pages');
            Schema::rename('participants', 'z_deprecated_participants');
            Schema::rename('payments', 'z_deprecated_payments');
            Schema::rename('pinned_posts', 'z_deprecated_pinned_posts');
            Schema::rename('post_likes', 'z_deprecated_post_likes');
            Schema::rename('post_media', 'z_deprecated_post_media');
            Schema::rename('post_reports', 'z_deprecated_post_reports');
            Schema::rename('post_tags', 'z_deprecated_post_tags');
            Schema::rename('saved_posts', 'z_deprecated_saved_posts');
            Schema::rename('saved_timelines', 'z_deprecated_saved_timelines');
            Schema::rename('static_pages', 'z_deprecated_static_pages');
            Schema::rename('threads', 'z_deprecated_threads');
            Schema::rename('timeline_reports', 'z_deprecated_timeline_reports');
            Schema::rename('user_list_types', 'z_deprecated_user_list_types');
            Schema::rename('user_lists', 'z_deprecated_user_lists');
            Schema::rename('wallpapers', 'z_deprecated_wallpapers');
        });
    }

    public function down()
    {
        Schema::table('albums', function (Blueprint $table) {
            Schema::rename('z_deprecated_followers', 'deprecated_followers');
            Schema::rename('z_deprecated_post_follows', 'deprecated_post_follows');
            Schema::rename('z_deprecated_post_shares', 'deprecated_post_shares');
            Schema::rename('z_deprecated_post_tips', 'deprecated_post_tips');
            Schema::rename('z_deprecated_purchased_posts', 'deprecated_purchased_posts');
            Schema::rename('z_deprecated_subscriptions', 'deprecated_subscriptions');
            Schema::rename('z_deprecated_users_tips', 'deprecated_users_tips');

            Schema::rename('z_deprecated_albums', 'albums');
            Schema::rename('z_deprecated_album_media', 'album_media');
            Schema::rename('z_deprecated_announcement_user', 'announcement_user');
            Schema::rename('z_deprecated_announcements', 'announcements');
            Schema::rename('z_deprecated_bank_account_details', 'bank_account_details');
            Schema::rename('z_deprecated_categories', 'categories');
            Schema::rename('z_deprecated_comment_likes', 'comment_likes');
            Schema::rename('z_deprecated_event_user', 'event_user');
            Schema::rename('z_deprecated_events', 'events');
            Schema::rename('z_deprecated_favourite_users', 'favourite_users');
            Schema::rename('z_deprecated_group_user', 'group_user');
            Schema::rename('z_deprecated_groups', 'groups');
            Schema::rename('z_deprecated_hashtags', 'hashtags');
            Schema::rename('z_deprecated_languages', 'languages');
            Schema::rename('z_deprecated_media', 'media');
            Schema::rename('z_deprecated_page_likes', 'page_likes');
            Schema::rename('z_deprecated_page_user', 'page_user');
            Schema::rename('z_deprecated_pages', 'pages');
            Schema::rename('z_deprecated_participants', 'participants');
            Schema::rename('z_deprecated_payments', 'payments');
            Schema::rename('z_deprecated_pinned_posts', 'pinned_posts');
            Schema::rename('z_deprecated_post_likes', 'post_likes');
            Schema::rename('z_deprecated_post_media', 'post_media');
            Schema::rename('z_deprecated_post_reports', 'post_reports');
            Schema::rename('z_deprecated_post_tags', 'post_tags');
            Schema::rename('z_deprecated_saved_posts', 'saved_posts');
            Schema::rename('z_deprecated_saved_timelines', 'saved_timelines');
            Schema::rename('z_deprecated_static_pages', 'static_pages');
            Schema::rename('z_deprecated_threads', 'threads');
            Schema::rename('z_deprecated_timeline_reports', 'timeline_reports');
            Schema::rename('z_deprecated_user_list_types', 'user_list_types');
            Schema::rename('z_deprecated_user_lists', 'user_lists');
            Schema::rename('z_deprecated_wallpapers', 'wallpapers');
        });
    }
}
