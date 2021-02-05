<?php

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
Route::post('ajax/switch-language', 'TimelineController@switchLanguage');

// Require all files in routes/web
$files = Finder::create()
    ->in(base_path('routes/web'))
    ->name('*.php');

foreach($files as $file) {
    require( $file->getRealPath() );
}

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
    Route::get('/browse', 'TimelineController@showGlobalFeed');
    Route::get('explore', 'TimelineController@showExplorePosts')->name('explore-posts');
    Route::get('purchased-posts', 'TimelineController@showPurchasedPosts')->name('purchased-posts');
    Route::get('post/{id}', 'TimelineController@showPost')->name('post.show');
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

Route::post('/member/update-role', 'TimelineController@assignMemberRole');
Route::post('/member/updatepage-role', 'TimelineController@assignPageMemberRole');
Route::get('/post/{post_id}', 'TimelineController@singlePost');

Route::get('allnotifications', 'TimelineController@allNotifications');
Route::get('/mylists', 'TimelineController@showMyLists');
Route::get('/mylist/{list_type_id}', 'TimelineController@showSpecificList');

Route::group(['prefix' => '/{username}', 'middleware' => ['auth', 'editown']], function ($username) {

    Route::get('/messages', 'UserController@messages');
    Route::get('/follow-requests', 'UserController@followRequests');

    Route::get('/pages-groups', 'TimelineController@pagesGroups');
    
    Route::get('/album/create', 'TimelineController@createAlbum');
    Route::post('/album/create', 'TimelineController@saveAlbum');

    Route::get('/album/{id}/edit', 'TimelineController@editAlbum');
    Route::post('/album/{id}/edit', 'TimelineController@updateAlbum');
    Route::get('/album/{album}/delete', 'TimelineController@deleteAlbum');

    Route::get('/album-preview/{id}/{photo_id}', 'TimelineController@addPreview');
    Route::get('/delete-media/{media}', 'TimelineController@deleteMedia');

    Route::post('/move-photos', 'UserController@movePhotos');
    Route::post('/delete-photos', 'UserController@deletePhotos');

    // Route::get('/pages', 'UserController@pages');
    // Route::get('/groups', 'UserController@groups');
    Route::get('/saved', 'UserController@savedItems'); // %FIXME %DEPRECATE, use new resource route for this

});

Route::group(['prefix' => '/{username}'], function ($username) {
    Route::get('/', 'TimelineController@posts')->name('users.profile');
    Route::post('/', 'TimelineController@posts');
});
/*
|--------------------------------------------------------------------------
| Page settings routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}/page-settings', 'middleware' => ['auth', 'editpage']], function ($username) {
    Route::get('/general', 'TimelineController@generalPageSettings');
    Route::post('/general', 'TimelineController@updateGeneralPageSettings');
    Route::get('/privacy', 'TimelineController@privacyPageSettings');
    Route::post('/privacy', 'TimelineController@updatePrivacyPageSettings');
    Route::get('/wallpaper', 'TimelineController@pageWallpaperSettings');
    Route::post('/wallpaper', 'TimelineController@saveWallpaperSettings');
    Route::get('/toggle-wallpaper/{action}/{media}', 'TimelineController@toggleWallpaper');
    Route::get('/roles', 'TimelineController@rolesPageSettings');
    Route::get('/likes', 'TimelineController@likesPageSettings');
});

/*
|--------------------------------------------------------------------------
| Group settings routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}/group-settings', 'middleware' => ['auth', 'editgroup']], function ($username) {
    Route::get('/general', 'TimelineController@groupGeneralSettings');
    Route::post('/general', 'TimelineController@updateUserGroupSettings');
    // Route::get('/closegroup', 'TimelineController@groupGeneralSettings');
    // Route::get('/join-requests/{group_id}', 'TimelineController@getJoinRequests');
    Route::get('/wallpaper', 'TimelineController@groupWallpaperSettings');
    Route::post('/wallpaper', 'TimelineController@saveWallpaperSettings');
    Route::get('/toggle-wallpaper/{action}/{media}', 'TimelineController@toggleWallpaper');
});

/*
|--------------------------------------------------------------------------
| Event settings routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}/event-settings', 'middleware' => ['auth','editevent']], function ($username) {
    Route::get('/general', 'TimelineController@generalEventSettings');
    Route::post('/general', 'TimelineController@updateUserEventSettings');
});

Route::group(['prefix' => 'ajax', 'middleware' => ['auth']], function () {

    Route::get('timeline-render-modal', 'TimelineController@renderModal'); // %PSG

    Route::post('get-youtube-video', 'TimelineController@getYoutubeVideo');
    Route::post('like-post', 'TimelineController@likePost');
    Route::post('notify-user', 'TimelineController@getNotifications');
    Route::post('post-comment', 'TimelineController@postComment');
    Route::post('page-like', 'TimelineController@pageLike');
    Route::post('change-avatar', 'TimelineController@changeAvatar');
    Route::post('change-cover', 'TimelineController@changeCover');
    Route::post('comment-like', 'TimelineController@likeComment');
    Route::post('comment-delete', 'TimelineController@deleteComment');
    Route::post('post-delete', 'TimelineController@deletePost');
    Route::post('page-delete', 'TimelineController@deletePage');
    Route::post('share-post', 'TimelineController@sharePost');
    Route::post('purchase-post/{post}', 'PostsController@purchase')->name('posts.purchase'); // %FIXME: move to above
    Route::post('send-tip-post/{post}', 'PostsController@tip')->name('posts.tip'); // %FIXME: move to above
    Route::post('send-tip-user/{user}', 'UsersController@tip')->name('users.tip');
    Route::post('page-liked', 'TimelineController@pageLiked');
    Route::get('get-more-posts', 'TimelineController@getMorePosts');
    Route::get('get-more-feed', 'TimelineController@showFeed');
    Route::get('get-global-feed', 'TimelineController@showGlobalFeed');
    Route::get('get-explore-posts', 'TimelineController@showExplorePosts');
    Route::get('get-purchased-posts', 'TimelineController@showPurchasedPosts');
    Route::get('search-explore-posts', 'TimelineController@searchExplorePosts')->name('post.search');
    Route::get('search-purchased-posts', 'TimelineController@searchPurchasedPosts')->name('purchased-post.search');
    
    Route::post('get-users', 'UserController@getUsersJoin');
    Route::get('get-users-mentions', 'UserController@getUsersMentions');

    Route::post('report-post', 'TimelineController@reportPost');
    Route::post('post-message/{id}', 'MessageController@update');
    Route::post('create-message', 'MessageController@store');
    Route::post('page-report', 'TimelineController@pageReport');
    Route::post('get-notifications', 'UserController@getNotifications');
    Route::post('get-unread-notifications', 'UserController@getUnreadNotifications');
    Route::post('get-messages', 'MessageController@getMessages')->name('conversation.get-messages');
    Route::post('favourite-user', 'MessageController@favouriteUser')->name('conversation.favourite-user');
    Route::post('get-message/{id}', 'MessageController@getMessage');
    Route::post('get-conversation/{id}', 'MessageController@show');
    Route::post('get-private-conversation/{userId}', 'MessageController@getPrivateConversation');
    Route::post('get-unread-message', 'UserController@getUnreadMessage');
    Route::post('get-unread-messages', 'MessageController@getUnreadMessages');

    Route::post('get-users-modal', 'UserController@getUsersModal');
    Route::post('edit-post', 'TimelineController@editPost');
    Route::get('load-emoji', 'TimelineController@loadEmoji');
    Route::post('update-post', 'TimelineController@updatePost');
    Route::post('/mark-all-notifications', 'NotificationController@markAllRead');
    // Route::post('add-page-members', 'UserController@addingMembersPage');
    // Route::post('get-members-join', 'UserController@getMembersJoin');
    // Route::post('announce-delete', 'AdminController@removeAnnouncement');
    // Route::post('category-delete', 'AdminController@removeCategory');
    Route::post('get-members-invite', 'UserController@getMembersInvite');
    // Route::post('add-event-members', 'UserController@addingEventMembers');
    // Route::post('join-event', 'TimelineController@joiningEvent');
    // Route::post('event-delete', 'TimelineController@deleteEvent');
    Route::post('notification-delete', 'TimelineController@deleteNotification');
    Route::post('allnotifications-delete', 'TimelineController@deleteAllNotifications');
    Route::post('post-hide', 'TimelineController@hidePost');
    // Route::post('group-delete', 'TimelineController@deleteGroup');
    Route::post('media-edit', 'TimelineController@albumPhotoEdit');
    // Route::post('unjoinPage', 'TimelineController@unjoinPage');
    Route::post('save-timeline', 'TimelineController@saveTimeline');
    Route::post('save-post', 'TimelineController@savePost');
    Route::post('pin-post', 'TimelineController@pinPost');
    Route::post('update-user-list', 'TimelineController@updateUserList');
    Route::post('get-user-list', 'TimelineController@getUserList');
    Route::post('add-new-user-list', 'TimelineController@addNewUserList');
    Route::post('get-lists-sort-by', 'TimelineController@getListsSortBy');

    // --

    Route::post('follow/{timeline}', 'TimelineController@follow')->name('timelines.follow');
});

Route::group(['prefix' => '/{username}', 'middleware' => 'auth'], function ($username) {
    // Route::get('/', 'TimelineController@posts');
    // Route::post('/', 'TimelineController@posts');

    Route::get('/followers', 'UserController@followers');
    Route::get('/following', 'UserController@following');

    Route::get('/event-guests', 'UserController@getGuestEvents');

    Route::get('/posts', 'TimelineController@posts');

    Route::get('/liked-pages', 'UserController@likedPages');
    Route::get('/joined-groups', 'UserController@joinedGroups');
    
    Route::get('/members/{group_id}', 'TimelineController@getGroupMember');
    
    Route::get('/notification/{id}', 'NotificationController@redirectNotification');

    Route::get('/albums', 'TimelineController@allAlbums');
    Route::get('/photos', 'TimelineController@allPhotos');
    Route::get('/videos', 'TimelineController@allVideos');
    Route::get('/album/show/{id}', 'TimelineController@viewAlbum');

});

Route::get('messages/{username?}', 'MessageController@index');
