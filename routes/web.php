<?php
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Route;

// Require all files in routes/web
$files = Finder::create()
    ->in(base_path('routes/web'))
    ->name('*.php');

foreach ($files as $file) {
    require($file->getRealPath());
}

/*
|--------------------------------------------------------------------------
| Auth 
|--------------------------------------------------------------------------
*/

/**
 * Removing due to limitations
 */
Route::group(['middleware' => ['web']], function () {
    Auth::routes();

    // Skip these to spa controller
    Route::get('/login', 'SpaController@index');
    Route::get('/register', 'SpaController@index');
    Route::get('/forgot-password', 'SpaController@index');

});

Route::get('facebook', 'Auth\RegisterController@facebookRedirect'); // auth redirect
Route::get('account/facebook', 'Auth\RegisterController@facebook'); // return url (?)

Route::get('google', 'Auth\RegisterController@googleRedirect');
Route::get('account/google', 'Auth\RegisterController@google');

Route::get('twitter', 'Auth\RegisterController@twitterRedirect');
Route::get('account/twitter', 'Auth\RegisterController@twitter');

/* ---------------------------------- Login --------------------------------- */
// Route::get('/login', 'Auth\LoginController@getLogin');
Route::post('/login', 'Auth\LoginController@login');
// Route::get('/login2', 'Auth\LoginController@login');

/* -------------------------------- Register -------------------------------- */
// Route::get('/register', 'Auth\RegisterController@register')->name('auth.register');
Route::post('/register', 'Auth\RegisterController@registerUser');
Route::post('/forgot-password', 'Auth\ForgotPasswordController@store');
Route::post('/password/reset/{token}', 'Auth\ForgotPasswordController@checkResetToken');
Route::post('/password/reset', 'Auth\ForgotPasswordController@resetPass');
// Route::get('email/verify', 'Auth\RegisterController@verifyEmail');

//main project register
// Route::get('/main-register', 'Auth\RegisterController@mainProjectRegister');
// Route::post('/main-login', 'Auth\LoginController@mainProjectLogin');
// Route::get('/main-user-update', 'Auth\RegisterController@mainUserUpdate');

/*
|--------------------------------------------------------------------------
| Resources
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth']], function () {

    Route::delete('/blockables/{user}/unblock/{slug}', ['as'=>'blockables.unblock', 'uses' => 'BlockablesController@unblock']);
    Route::get('/blockables/match', ['as'=>'blockables.match', 'uses' => 'BlockablesController@match']);

    // -- chatmessages --
    Route::resource('chatmessages', 'ChatmessagesController', [
        'only' => [ 'index', ],
    ]);

    // -- chatthreads --
    Route::get('/chatthreads/totalUnreadCount', ['as'=>'chatthreads.totalUnreadCount', 'uses' => 'ChatthreadsController@getTotalUnreadCount']);
    Route::post('/chatthreads/markAllRead', ['as'=>'chatthreads.markAllRead', 'uses' => 'ChatthreadsController@markAllRead']);
    Route::post('/chatthreads/{chatthread}/sendMessage', ['as'=>'chatthreads.sendMessage', 'uses' => 'ChatthreadsController@sendMessage']);
    Route::post('/chatthreads/{chatthread}/markRead', ['as'=>'chatthreads.markRead', 'uses' => 'ChatthreadsController@markRead']);
    Route::post('/chatthreads/{chatthread}/scheduleMessage', ['as'=>'chatthreads.scheduleMessage', 'uses' => 'ChatthreadsController@scheduleMessage']);
    Route::get('/chatthreads/search', 'ChatthreadsController@search')->name('chatthreads.search');
    Route::resource('chatthreads', 'ChatthreadsController', [
        'only' => [ 'index', 'show', 'store' ],
    ]);

    // -- mycontacts --
    // apiResource Routes:
    // https://laravel.com/docs/8.x/controllers#actions-handled-by-resource-controller
    // https://laravel.com/docs/8.x/controllers#api-resource-routes
    Route::get('/mycontacts/search', 'MycontactsController@search')
        ->name('mycontacts.search');
    Route::apiResource('mycontacts', 'MycontactsController');

    // -- comments: likeable --
    Route::get('/comments/match', ['as'=>'comments.match', 'uses' => 'CommentsController@match']);
    Route::resource('comments', 'CommentsController', [ 'except' => ['create','edit'] ]);

    Route::get('/fanledgers/{user}/earnings', ['as'=>'fanledgers.showEarnings', 'uses' => 'FanledgersController@showEarnings']);
    Route::get('/fanledgers/{user}/debits', ['as'=>'fanledgers.showDebits', 'uses' => 'FanledgersController@showDebits']);
    Route::resource('fanledgers', 'FanledgersController', [
        'only' => [ 'index', ],
    ]);

    Route::post('/invites/{vaultfolder}/share', ['as'=>'invites.shareVaultResources', 'uses' => 'InvitesController@shareVaultResources']);
    Route::resource('invites', 'InvitesController', [ 
        'only' => ['index', 'show', 'store'],
    ]);

    // -- likeables:  --
    // ~ PUT /api/v1/resource/:id/likes/:userid
    // ~ DELETE /api/v1/resource/:id/likes/:userid
    // see: https://stackoverflow.com/questions/5665893/the-rest-way-to-check-uncheck-like-unlike-favorite-unfavorite-a-resource
    Route::put('/likeables/{liker}', ['as'=>'likeables.update', 'uses' => 'LikeablesController@update']); // like
    Route::delete('/likeables/{liker}', ['as'=>'likeables.destroy', 'uses' => 'LikeablesController@destroy']); // unlike
    Route::resource('likeables', 'LikeablesController', [
        'only' => [ 'index' ],
    ]);

    // -- favorites:  --
    Route::post('/favorites/remove', ['as'=>'favorites.remove', 'uses' => 'FavoritesController@remove']); // remove favorites w/o pkid
    Route::resource('favorites', 'FavoritesController', [
        'except' => [ 'create', 'edit', 'update', ],
    ]);

    // -- mediafiles: likeable | shareable | commentable (?) | tippable | purchaseable --
    //Route::post('/mediafiles/{mediafile}/doClone', ['as'=>'mediafiles.doClone', 'uses' => 'MediafilesController@doClone']);
    Route::get('/mediafiles/match', ['as'=>'mediafiles.match', 'uses' => 'MediafilesController@match']);
    Route::resource('mediafiles', 'MediafilesController', [ 'except' => [ 'create', 'edit', ] ]);

    Route::resource('notifications', 'NotificationsController', [ 'only' => [ 'index', ] ]);

    /* -------------------------------- Posts ------------------------------- */
    // -- posts: likeable | shareable | commentable | tippable | purchaseable | pinnable --
    #region Posts
    Route::group(['prefix' => '/posts'], function () {
        Route::get('/match', 'PostsController@match')
            ->name('posts.match');

        // tippable
        Route::put('/{post}/tip', 'PostsController@tip')
            ->name('posts.tip');

        // purchaseable
        Route::put('/{post}/purchase', 'PostsController@purchase')
            ->name('posts.purchase');

        Route::get('/{post}/index-comments', 'PostsController@indexComments')
            ->name('posts.indexComments');

    });
    Route::apiResource('posts', 'PostsController');
    #endregion Posts

    /* ------------------------------ Sessions ------------------------------ */
    #region Sessions
    Route::resource('sessions', 'SessionsController', [ 'only' => [ 'index' ] ]);

    #endregion Sessions

    // -- stories:  --
    Route::get('/stories/player', ['as' => 'stories.player', 'uses' => 'SpaController@index']);
    Route::get('/stories/match', ['as'=>'stories.match', 'uses' => 'StoriesController@match']);
    Route::get('/stories/dashboard', [
        'middleware' => 'spaMixedRoute',
        'as' => 'stories.dashboard',
        'uses' => 'StoriesController@dashboard'
    ]);
    Route::resource('stories', 'StoriesController', [ 'except' => [ 'create', 'edit', ] ]);

    // -- shareables:  --
    Route::get('/shareables/indexFollowers', ['as' => 'shareables.indexFollowers', 'uses' => 'ShareablesController@indexFollowers']);
    Route::get('/shareables/indexFollowing', ['as' => 'shareables.indexFollowing', 'uses' => 'ShareablesController@indexFollowing']);
    Route::resource('shareables', 'ShareablesController', [
        'only' => [ 'index', ],
    ]);

    // -- timelines: tippable | subscribeable | followable --
    #region Timelines
    Route::get('/timelines-suggested', ['as'=>'timelines.suggested', 'uses' => 'TimelinesController@suggested']); // %FIXME: refactor: use index(?)
    //Route::get('/timelines/home', ['as'=>'timelines.home', 'uses' => 'TimelinesController@home']); // special case of 'show'
    Route::get('/timelines/match', ['as'=>'timelines.match', 'uses' => 'TimelinesController@match']);
    Route::get('/timelines/home/feed', ['as'=>'timelines.homefeed', 'uses' => 'TimelinesController@homefeed']);
    Route::get('/timelines/home/scheduled-feed', ['as'=>'timelines.homescheduledfeed', 'uses' => 'TimelinesController@homeScheduledfeed']);
    Route::get('/timelines/{timeline}/feed', ['as'=>'timelines.feed', 'uses' => 'TimelinesController@feed']);
    Route::get('/timelines/{timeline}/photos', ['as'=>'timelines.photos', 'uses' => 'TimelinesController@photos']); // photos feed
    Route::get('/timelines/{timeline}/videos', ['as'=>'timelines.videos', 'uses' => 'TimelinesController@videos']); // videos feed
    Route::get('/timelines/{timeline}/preview-posts', ['as'=>'timelines.previewPosts', 'uses' => 'TimelinesController@previewPosts']);

    Route::put('/timelines/{timeline}/tip', ['as'=>'timelines.tip', 'uses' => 'TimelinesController@tip']);

    Route::put('/timelines/{timeline}/follow', ['as'=>'timelines.follow', 'uses' => 'TimelinesController@follow']);

    Route::put('/timelines/{timeline}/subscribe', ['as'=>'timelines.subscribe', 'uses' => 'TimelinesController@subscribe']);

    Route::put('/timelines/{timeline}/unsubscribe', ['as' => 'timelines.unsubscribe', 'uses' => 'TimelinesController@unsubscribe']);
    Route::resource('timelines', 'TimelinesController', [
        'only' => ['index', 'show'],
    ]);

    #endregion Timelines

    // -- users: messageable --
    //Route::get('/users-suggested', ['as'=>'users.suggested', 'uses' => 'UsersController@suggested']);
    Route::get('/users/me', ['as' => 'users.me', 'uses' => 'UsersController@me']);
    Route::get('/users/match', ['as'=>'users.match', 'uses' => 'UsersController@match']);
    Route::patch('/users/{user}/settings/enable/{group}', ['as'=>'users.enableSetting', 'uses' => 'UsersController@enableSetting']); // turn on a single update within a group
    Route::patch('/users/{user}/settings/disable/{group}', ['as'=>'users.disableSetting', 'uses' => 'UsersController@disableSetting']); // turn off a single update within a group
    Route::patch('/users/{user}/settings', ['as'=>'users.updateSettingsBatch', 'uses' => 'UsersController@updateSettingsBatch']); // batch update one (or multiple) groups at a time
    Route::patch('/users/{user}/updatePassword', ['as'=>'users.updatePassword', 'uses' => 'UsersController@updatePassword']);
    Route::get('/users/{user}/settings', [
        'middleware' => 'spaMixedRoute',
        'as'=>'users.showSettings',
        'uses' => 'UsersController@showSettings',
    ]);
    Route::post('/users/avatar', ['as' => 'users.updateAvatar', 'uses' => 'UsersController@updateAvatar']);
    Route::post('/users/cover', ['as' => 'users.updateCover', 'uses' => 'UsersController@updateCover']);
    Route::resource('users', 'UsersController', [ 'except' => [ 'create', 'edit', 'store' ] ]);

    // -- vaults:  --
    Route::get('/my-vault', [
        'middleware' => 'spaMixedRoute',
        'as' => 'vault.dashboard',
        'uses' => 'VaultsController@dashboard'
    ]);
    //Route::get('/vaults/all-files', ['as'=>'vaults.getAllFiles', 'uses' => 'VaultsController@getAllFiles']); DEPRECATED - use .index route 
    Route::get('/vaults/{vault}/getRootFolder', ['as'=>'vaults.getRootFolder', 'uses' => 'VaultsController@getRootFolder']);
    Route::patch('/vaults/{vault}/updateShares', ['as'=>'vaults.updateShares', 'uses' => 'VaultsController@updateShares']); // %FIXME: refactor to make consistent
    Route::resource('vaults', 'VaultsController', [
        'only' => ['index', 'show'],
    ]);

    // -- vaultfolders: shareable | purchaseable --
    Route::get('/vaultfolders/match', ['as'=>'vaultfolders.match', 'uses' => 'VaultfoldersController@match']);
    Route::post('/vaultfolders/{vaultfolder}/share', ['as'=>'vaultfolders.share', 'uses' => 'VaultfoldersController@share']);
    Route::resource('vaultfolders', 'VaultfoldersController', [ ]);

    // -- misc --
    Route::post('update-last-seen', 'UsersController@updateLastSeen')->name('update-user-status');
    /*
    Route::get('/saved/dashboard', ['as'=>'saved.dashboard', 'uses' => 'SaveditemsController@dashboard']);
    Route::resource('saved', 'SaveditemsController', [
        'only' => ['index', 'show', 'store'],
    ]);
     */

});

// DEPRECATED
//  -- messages --
//Route::get('/chat-messages/users', ['as'=>'messages.fetchusers', 'uses' => 'MessageController@fetchUsers']);
//Route::get('/chat-messages/contacts', ['as'=>'messages.fetchcontacts', 'uses' => 'MessageController@fetchContacts']);
//Route::get('/chat-messages/scheduled', ['as'=>'messages.fetchscheduled', 'uses' => 'MessageController@fetchScheduled']);
//Route::delete('/chat-messages/scheduled/{threadId}', ['as'=>'messages.removeschedule', 'uses' => 'MessageController@removeScheduleThread']);
//Route::patch('/chat-messages/scheduled/{threadId}', ['as'=>'messages.editschedule', 'uses' => 'MessageController@editScheduleThread']);
//Route::get('/chat-messages/{id}', ['as'=>'messages.fetchcontact', 'uses' => 'MessageController@fetchcontact']);
//Route::delete('/chat-messages/{id}', ['as'=>'messages.clearcontact', 'uses' => 'MessageController@clearUser']);
//Route::post('/chat-messages/{id}/mark-as-read', ['as'=>'messages.markasread', 'uses' => 'MessageController@markAsRead']);
//Route::post('/chat-messages/mark-all-as-read', ['as'=>'messages.markallasread', 'uses' => 'MessageController@markAllAsRead']);
//Route::get('/chat-messages/{id}/search', ['as'=>'messages.filtermessages', 'uses' => 'MessageController@filterMessages']);
//Route::patch('/chat-messages/{id}/mute', ['as'=>'messages.mute', 'uses' => 'MessageController@mute']);
//Route::patch('/chat-messages/{id}/unmute', ['as'=>'messages.unmute', 'uses' => 'MessageController@unmute']);
//Route::post('/chat-messages/{id}/custom-name', ['as'=>'messages.customname', 'uses' => 'MessageController@setCustomName']);
//Route::get('/chat-messages/{id}/mediafiles', ['as'=>'messages.mediafiles', 'uses' => 'MessageController@listMediafiles']);
//Route::delete('/chat-messages/{id}/threads/{threadId}', ['as'=>'messages.removethread', 'uses' => 'MessageController@removeThread']);
//Route::post('/chat-messages/{id}/threads/{threadId}/like', ['as'=>'messages.setlike', 'uses' => 'MessageController@setLike']);
//Route::post('/chat-messages/{id}/threads/{threadId}/unlike', ['as'=>'messages.setunlike', 'uses' => 'MessageController@setUnlike']);
//
//Route::resource('chat-messages', 'MessageController')->only([
//    'index',
//    'store'
//]);

/*
|--------------------------------------------------------------------------
| UsernameRules 
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => '/username'], function() {
    Route::match(['get', 'post'], '/check/{username?}', 'UsernameRulesController@checkUsername')->name('usernameRules.check');

    // Admin Crud API //
    Route::group(['middleware' => ['auth', 'role:admin']], function() {
        Route::get('/rules', 'UsernameRulesController@index')->name('usernameRules.index');
        Route::get('/rules/{page}', 'UsernameRulesController@list')->name('usernameRules.list');
        Route::get('/rule/new', 'UsernameRulesController@create')->name('usernameRules.create');
        Route::post('/rule', 'UsernameRulesController@store')->name('usernameRules.store');
        Route::get('/rule/{id}', 'UsernameRulesController@show')->name('usernameRules.show');
        Route::match(['put', 'patch'], '/rule', 'UsernameRulesController@update')->name('usernameRules.update');
        Route::delete('/rule', 'UsernameRulesController@destroy')->name('usernameRules.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Pusher
|--------------------------------------------------------------------------
*/

Route::post('pusher/auth', function (Illuminate\Http\Request $request, Pusher $pusher) {
    return $pusher->presence_auth(
        $request->input('channel_name'),
        $request->input('socket_id'),
        uniqid(),
        ['username' => $request->input('username')]
    );
});

/*
|--------------------------------------------------------------------------
| Static Pages
|--------------------------------------------------------------------------
*/
/*
Route::get('faq', 'PageController@faq');
Route::get('support', 'PageController@support');
Route::get('terms-of-use', 'PageController@termsOfUse');
Route::get('privacy-policy', 'PageController@privacyPolicy');
Route::get('dmca', 'PageController@dmca');
Route::get('usc2257', 'PageController@usc2257');
Route::get('legal', 'PageController@legal');
Route::get('blog', 'PageController@blog');
*/

/*
|--------------------------------------------------------------------------
| Misc
|--------------------------------------------------------------------------
*/

Route::get('/home', 'HomeController@index');

Route::get('/search', 'SearchController@search')->name('search');
Route::post('/search', 'SearchController@search')->name('search.post');

Route::get('/lists', 'ListsController@index')->name('lists.index');
Route::post('/lists', 'ListsController@store')->name('lists.store');
Route::post('/lists/{id}/users', 'ListsController@addUserToList')->name('lists.adduser');
Route::delete('/lists/{id}/users/{userId}', 'ListsController@removeUserFromList')->name('lists.removeuser');
Route::post('/lists/{id}/pin', 'ListsController@addToPin')->name('lists.addtopin');
Route::delete('/lists/{id}/pin', 'ListsController@removeFromPin')->name('lists.removefrompin');

/**
 * Single Page application catch all undefined routes
 * Laravel router will first try to match static resources, then specific routes, then finally this.
 */
Route::get('/{any}', 'SpaController@index')->name('spa.index')->where('any', '.*');
