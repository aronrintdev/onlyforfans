<?php

/**
 * Deprecated Routes
 */

// Webhooks
Route::post('webhook', 'CheckoutController@webhook');
Route::post('/register/{affliate}', 'Auth\RegisterController@registerUser');
Route::get('webhook', 'CheckoutController@webhook');

Route::get('/contact', 'PageController@contact');
Route::post('/contact', 'PageController@saveContact');
Route::get('/share-post/{id}', 'PageController@sharePost');
Route::get('/post/{id}', 'PageController@sharePost');
Route::get('/get-location/{location}', 'HomeController@getLocation');

Route::get('check-php', function () {
    return phpinfo();
});
Route::post('ajax/switch-language', 'ZDeprecated\TimelineController@switchLanguage');


// Quick testing debug route
Route::get('testing', function() {
    if (env('APP_DEBUG')) {
        return view('testing')->with(['user' => Auth::user()]);
    }
    return response('', 404);
});

Route::group(['prefix' => 'api', 'middleware' => ['auth', 'cors'], 'namespace' => 'API'], function () {
    Route::group(['prefix' => 'v1'], function () {
        require config('infyom.laravel_generator.path.api_routes');
    });
});


Route::get('failed-payment', 'CheckoutController@handleFailedPayment')->name('failed-payment');
// Stripe
Route::group(['prefix' => 'checkout', 'middleware' => ['auth']], function() {
    Route::post('create-checkout-session/{timeline_id}', 'CheckoutController@createCheckoutSession');
    Route::get('create-checkout-session/{timeline_id}', 'CheckoutController@createCheckoutSession');
    Route::post('create-post-checkout-session/{post_id}', 'CheckoutController@createPostCheckoutSession');
    Route::get('create-post-checkout-session/{post_id}', 'CheckoutController@createPostCheckoutSession');
    Route::get('checkout-session', 'CheckoutController@checkoutSession');
    Route::post('config/{timeline_id}', 'CheckoutController@getConfig');
    Route::get('payment-success', 'CheckoutController@paymentSuccess')->name('payment-success');

    // connected account
    Route::post('get-oauth-link', 'CheckoutController@getOAuthLink');
    Route::get('get-oauth-link', 'CheckoutController@getOAuthLink');
    Route::post('/authorize-oauth', 'CheckoutController@authorizeOAuth');
    Route::get('/authorize-oauth', 'CheckoutController@authorizeOAuth');

});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/browse', 'ZDeprecated\TimelineController@showGlobalFeed');
    Route::get('explore', 'ZDeprecated\TimelineController@showExplorePosts')->name('explore-posts');
    Route::get('purchased-posts', 'ZDeprecated\TimelineController@showPurchasedPosts')->name('purchased-posts');
    Route::get('post/{id}', 'ZDeprecated\TimelineController@showPost')->name('post.show');
});

/*
|--------------------------------------------------------------------------
| Image routes
|--------------------------------------------------------------------------
*/

Route::get('user/avatar/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/users/avatars/'.$filename)->response();
});

Route::get('user/cover/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/users/covers/'.$filename)->response();
});

Route::get('user/gallery/video/{filename}', function ($filename) {
    $fileContents = Storage::disk('uploads')->get("users/gallery/{$filename}");
    $response = Response::make($fileContents, 200);
    $response->header('Content-Type', 'video/mp4');

    return $response;
});

Route::get('user/gallery/{filename}', function ($filename) {
    try {
        return Image::make(storage_path().'/uploads/users/gallery/'.$filename)->response();
    } catch (\Exception $e) {
        $defaultFN = 'default-cover-user.png';
        return Image::make(storage_path().'/uploads/users/covers/'.$defaultFN)->response();
    }
});


Route::get('page/avatar/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/pages/avatars/'.$filename)->response();
});

Route::get('page/cover/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/pages/covers/'.$filename)->response();
});

Route::get('group/avatar/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/groups/avatars/'.$filename)->response();
});

Route::get('group/cover/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/groups/covers/'.$filename)->response();
});

Route::get('setting/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/settings/'.$filename)->response();
});

Route::get('event/cover/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/events/covers/'.$filename)->response();
});

Route::get('event/avatar/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/events/avatars/'.$filename)->response();
});

Route::get('/page/{pagename}', 'PageController@page');

Route::get('album/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/albums/'.$filename)->response();
});

Route::get('wallpaper/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/wallpapers/'.$filename)->response();
});

Route::post('/member/update-role', 'ZDeprecated\TimelineController@assignMemberRole');
Route::post('/member/updatepage-role', 'ZDeprecated\TimelineController@assignPageMemberRole');
Route::get('/post/{post_id}', 'ZDeprecated\TimelineController@singlePost');

Route::get('allnotifications', 'ZDeprecated\TimelineController@allNotifications');
Route::get('/mylists', 'ZDeprecated\TimelineController@showMyLists');
Route::get('/mylist/{list_type_id}', 'ZDeprecated\TimelineController@showSpecificList');

Route::group(['prefix' => '/{username}', 'middleware' => ['auth', 'editown']], function ($username) {

    Route::get('/messages', 'ZDeprecated\UserController@messages');
    Route::get('/follow-requests', 'ZDeprecated\UserController@followRequests');

    Route::get('/pages-groups', 'ZDeprecated\TimelineController@pagesGroups');
    
    Route::get('/album/create', 'ZDeprecated\TimelineController@createAlbum');
    Route::post('/album/create', 'ZDeprecated\TimelineController@saveAlbum');

    Route::get('/album/{id}/edit', 'ZDeprecated\TimelineController@editAlbum');
    Route::post('/album/{id}/edit', 'ZDeprecated\TimelineController@updateAlbum');
    Route::get('/album/{album}/delete', 'ZDeprecated\TimelineController@deleteAlbum');

    Route::get('/album-preview/{id}/{photo_id}', 'ZDeprecated\TimelineController@addPreview');
    Route::get('/delete-media/{media}', 'ZDeprecated\TimelineController@deleteMedia');

    Route::post('/move-photos', 'ZDeprecated\UserController@movePhotos');
    Route::post('/delete-photos', 'ZDeprecated\UserController@deletePhotos');

    // Route::get('/pages', 'ZDeprecated\UserController@pages');
    // Route::get('/groups', 'ZDeprecated\UserController@groups');
    Route::get('/saved', 'ZDeprecated\UserController@savedItems'); // %FIXME %DEPRECATE, use new resource route for this

});

Route::group(['prefix' => '/{username}'], function ($username) {
    Route::get('/', 'ZDeprecated\TimelineController@posts')->name('users.profile');
    Route::post('/', 'ZDeprecated\TimelineController@posts');
});
/*
|--------------------------------------------------------------------------
| Page settings routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}/page-settings', 'middleware' => ['auth', 'editpage']], function ($username) {
    Route::get('/general', 'ZDeprecated\TimelineController@generalPageSettings');
    Route::post('/general', 'ZDeprecated\TimelineController@updateGeneralPageSettings');
    Route::get('/privacy', 'ZDeprecated\TimelineController@privacyPageSettings');
    Route::post('/privacy', 'ZDeprecated\TimelineController@updatePrivacyPageSettings');
    Route::get('/wallpaper', 'ZDeprecated\TimelineController@pageWallpaperSettings');
    Route::post('/wallpaper', 'ZDeprecated\TimelineController@saveWallpaperSettings');
    Route::get('/toggle-wallpaper/{action}/{media}', 'ZDeprecated\TimelineController@toggleWallpaper');
    Route::get('/roles', 'ZDeprecated\TimelineController@rolesPageSettings');
    Route::get('/likes', 'ZDeprecated\TimelineController@likesPageSettings');
});

/*
|--------------------------------------------------------------------------
| Group settings routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}/group-settings', 'middleware' => ['auth', 'editgroup']], function ($username) {
    Route::get('/general', 'ZDeprecated\TimelineController@groupGeneralSettings');
    Route::post('/general', 'ZDeprecated\TimelineController@updateUserGroupSettings');
    // Route::get('/closegroup', 'ZDeprecated\TimelineController@groupGeneralSettings');
    // Route::get('/join-requests/{group_id}', 'ZDeprecated\TimelineController@getJoinRequests');
    Route::get('/wallpaper', 'ZDeprecated\TimelineController@groupWallpaperSettings');
    Route::post('/wallpaper', 'ZDeprecated\TimelineController@saveWallpaperSettings');
    Route::get('/toggle-wallpaper/{action}/{media}', 'ZDeprecated\TimelineController@toggleWallpaper');
});

/*
|--------------------------------------------------------------------------
| Event settings routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}/event-settings', 'middleware' => ['auth','editevent']], function ($username) {
    Route::get('/general', 'ZDeprecated\TimelineController@generalEventSettings');
    Route::post('/general', 'ZDeprecated\TimelineController@updateUserEventSettings');
});

Route::group(['prefix' => 'ajax', 'middleware' => ['auth']], function () {

    Route::get('timeline-render-modal', 'ZDeprecated\TimelineController@renderModal'); // %PSG

    Route::post('get-youtube-video', 'ZDeprecated\TimelineController@getYoutubeVideo');
    Route::post('like-post', 'ZDeprecated\TimelineController@likePost');
    Route::post('notify-user', 'ZDeprecated\TimelineController@getNotifications');
    Route::post('post-comment', 'ZDeprecated\TimelineController@postComment');
    Route::post('page-like', 'ZDeprecated\TimelineController@pageLike');
    Route::post('change-avatar', 'ZDeprecated\TimelineController@changeAvatar');
    Route::post('change-cover', 'ZDeprecated\TimelineController@changeCover');
    Route::post('comment-like', 'ZDeprecated\TimelineController@likeComment');
    Route::post('comment-delete', 'ZDeprecated\TimelineController@deleteComment');
    Route::post('post-delete', 'ZDeprecated\TimelineController@deletePost');
    Route::post('page-delete', 'ZDeprecated\TimelineController@deletePage');
    Route::post('share-post', 'ZDeprecated\TimelineController@sharePost');
    Route::post('purchase-post/{post}', 'PostsController@purchase')->name('posts.purchase'); // %FIXME: move to above
    Route::post('send-tip-post/{post}', 'PostsController@tip')->name('posts.tip'); // %FIXME: move to above
    Route::post('send-tip-user/{user}', 'UsersController@tip')->name('users.tip');
    Route::post('page-liked', 'ZDeprecated\TimelineController@pageLiked');
    Route::get('get-more-posts', 'ZDeprecated\TimelineController@getMorePosts');
    Route::get('get-more-feed', 'ZDeprecated\TimelineController@showFeed');
    Route::get('get-global-feed', 'ZDeprecated\TimelineController@showGlobalFeed');
    Route::get('get-explore-posts', 'ZDeprecated\TimelineController@showExplorePosts');
    Route::get('get-purchased-posts', 'ZDeprecated\TimelineController@showPurchasedPosts');
    Route::get('search-explore-posts', 'ZDeprecated\TimelineController@searchExplorePosts')->name('post.search');
    Route::get('search-purchased-posts', 'ZDeprecated\TimelineController@searchPurchasedPosts')->name('purchased-post.search');
    
    Route::post('get-users', 'ZDeprecated\UserController@getUsersJoin');
    Route::get('get-users-mentions', 'ZDeprecated\UserController@getUsersMentions');

    Route::post('report-post', 'ZDeprecated\TimelineController@reportPost');
    Route::post('post-message/{id}', 'MessageController@update');
    Route::post('create-message', 'MessageController@store');
    Route::post('page-report', 'ZDeprecated\TimelineController@pageReport');
    Route::post('get-notifications', 'ZDeprecated\UserController@getNotifications');
    Route::post('get-unread-notifications', 'ZDeprecated\UserController@getUnreadNotifications');
    Route::post('get-messages', 'MessageController@getMessages')->name('conversation.get-messages');
    Route::post('favourite-user', 'MessageController@favouriteUser')->name('conversation.favourite-user');
    Route::post('get-message/{id}', 'MessageController@getMessage');
    Route::post('get-conversation/{id}', 'MessageController@show');
    Route::post('get-private-conversation/{userId}', 'MessageController@getPrivateConversation');
    Route::post('get-unread-message', 'ZDeprecated\UserController@getUnreadMessage');
    Route::post('get-unread-messages', 'MessageController@getUnreadMessages');

    Route::post('get-users-modal', 'ZDeprecated\UserController@getUsersModal');
    Route::post('edit-post', 'ZDeprecated\TimelineController@editPost');
    Route::get('load-emoji', 'ZDeprecated\TimelineController@loadEmoji');
    Route::post('update-post', 'ZDeprecated\TimelineController@updatePost');
    Route::post('/mark-all-notifications', 'NotificationController@markAllRead');
    // Route::post('add-page-members', 'ZDeprecated\UserController@addingMembersPage');
    // Route::post('get-members-join', 'ZDeprecated\UserController@getMembersJoin');
    // Route::post('announce-delete', 'AdminController@removeAnnouncement');
    // Route::post('category-delete', 'AdminController@removeCategory');
    Route::post('get-members-invite', 'ZDeprecated\UserController@getMembersInvite');
    // Route::post('add-event-members', 'ZDeprecated\UserController@addingEventMembers');
    // Route::post('join-event', 'ZDeprecated\TimelineController@joiningEvent');
    // Route::post('event-delete', 'ZDeprecated\TimelineController@deleteEvent');
    Route::post('notification-delete', 'ZDeprecated\TimelineController@deleteNotification');
    Route::post('allnotifications-delete', 'ZDeprecated\TimelineController@deleteAllNotifications');
    Route::post('post-hide', 'ZDeprecated\TimelineController@hidePost');
    // Route::post('group-delete', 'ZDeprecated\TimelineController@deleteGroup');
    Route::post('media-edit', 'ZDeprecated\TimelineController@albumPhotoEdit');
    // Route::post('unjoinPage', 'ZDeprecated\TimelineController@unjoinPage');
    Route::post('save-timeline', 'ZDeprecated\TimelineController@saveTimeline');
    Route::post('save-post', 'ZDeprecated\TimelineController@savePost');
    Route::post('pin-post', 'ZDeprecated\TimelineController@pinPost');
    Route::post('update-user-list', 'ZDeprecated\TimelineController@updateUserList');
    Route::post('get-user-list', 'ZDeprecated\TimelineController@getUserList');
    Route::post('add-new-user-list', 'ZDeprecated\TimelineController@addNewUserList');
    Route::post('get-lists-sort-by', 'ZDeprecated\TimelineController@getListsSortBy');

    // --

    Route::post('follow/{timeline}', 'ZDeprecated\TimelineController@follow')->name('timelines.follow');
});

Route::group(['prefix' => '/{username}', 'middleware' => 'auth'], function ($username) {
    // Route::get('/', 'ZDeprecated\TimelineController@posts');
    // Route::post('/', 'ZDeprecated\TimelineController@posts');

    Route::get('/followers', 'ZDeprecated\UserController@followers');
    Route::get('/following', 'ZDeprecated\UserController@following');

    Route::get('/event-guests', 'ZDeprecated\UserController@getGuestEvents');

    Route::get('/posts', 'ZDeprecated\TimelineController@posts');

    Route::get('/liked-pages', 'ZDeprecated\UserController@likedPages');
    Route::get('/joined-groups', 'ZDeprecated\UserController@joinedGroups');
    
    Route::get('/members/{group_id}', 'ZDeprecated\TimelineController@getGroupMember');
    
    Route::get('/notification/{id}', 'NotificationController@redirectNotification');

    Route::get('/albums', 'ZDeprecated\TimelineController@allAlbums');
    Route::get('/photos', 'ZDeprecated\TimelineController@allPhotos');
    Route::get('/videos', 'ZDeprecated\TimelineController@allVideos');
    Route::get('/album/show/{id}', 'ZDeprecated\TimelineController@viewAlbum');

});

Route::get('messages/{username?}', 'MessageController@index');
