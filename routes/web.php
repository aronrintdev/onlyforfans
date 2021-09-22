<?php
use Pusher\Pusher;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', 'Auth\RegisterController@verifyEmail')->name('verification.verify');
    Route::post('/email/verify/resend', 'Auth\RegisterController@resendVerifyEmail')->name('verification.resend');

    // Skip these to spa controller
    Route::get('/login', 'SpaController@index');
    Route::get('/register', 'SpaController@index')->name('register');
    Route::get('/register/confirm-email', 'SpaController@index');
    Route::get('/forgot-password', 'SpaController@index');
    Route::get('/email/verified', 'SpaController@index');

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
    Route::get('/chatmessages/search', 'ChatmessagesController@search')->name('chatmessages.search');
    Route::post('/chatmessage/{chatmessage}/media', "ChatmessagesController@attachMedia")->name('chatmessages.attachMedia');
    Route::put('/chatmessage/{chatmessage}/purchase', 'ChatmessagesController@purchase')
        ->name('chatmessages.purchase');
    Route::apiResource('chatmessages', 'ChatmessagesController', [
        'only' => [ 'index', 'show', 'destroy' ],
    ]);

    Route::get('/chatmessagegroups/queue', 'ChatmessagegroupsController@queue')->name('chatmessagegroups.queue');
    Route::get('/chatmessagegroups/{chatmessagegroup}/unsend', 'ChatmessagegroupsController@unsend')->name('chatmessagegroups.unsend');
    Route::apiResource('chatmessagegroups', 'ChatmessagegroupsController', [
        'only' => [ 'index', ],
    ]);

    Route::get('/chatthreads/{chatthread}/gallery', 'ChatmessagesController@gallery')->name('chatthreads.gallery');

    // -- chatthreads --
    Route::get( '/chatthreads/totalUnreadCount',             ['as'=>'chatthreads.totalUnreadCount',      'uses' => 'ChatthreadsController@getTotalUnreadCount']);
    Route::get( '/chatthreads/search',                       ['as'=>'chatthreads.search',                'uses' => 'ChatthreadsController@search']);
    Route::get( '/chatthreads/{chatthread}/getMuteStatus',   ['as'=>'chatthreads.getMuteStatus',         'uses' => 'ChatthreadsController@getMuteStatus']);
    Route::post('/chatthreads/markAllRead',                  ['as'=>'chatthreads.markAllRead',           'uses' => 'ChatthreadsController@markAllRead']);
    Route::post('/chatthreads/{chatthread}/markRead',        ['as'=>'chatthreads.markRead',              'uses' => 'ChatthreadsController@markRead']);
    Route::post('/chatthreads/{chatthread}/toggleMute',      ['as'=>'chatthreads.toggleMute',            'uses' => 'ChatthreadsController@toggleMute']);
    Route::post('/chatthreads/{chatthread}/addMessage',      ['as'=>'chatthreads.addMessage',            'uses' => 'ChatthreadsController@addMessage']);
    Route::post('/chatthreads/findOrCreateDirect',           ['as'=>'chatthreads.findOrCreateDirect',    'uses' => 'ChatthreadsController@findOrCreateDirect']);
    Route::resource('chatthreads', 'ChatthreadsController', [
        'only' => [ 'index', 'show', 'store' ],
    ]);

    // -- contentflags --
    Route::resource('contentflags', 'ContentflagsController', [ 'except' => [ 'create', 'edit' ] ]);

    // -- contenttags --
    Route::resource('contenttags', 'ContenttagsController', [ 'only' => [ 'index', 'show' ] ]);

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

    // -- referrals:  --
    Route::resource('referrals', 'ReferralsController', [
        'only' => ['index'],
    ]);

    // -- notes: --
    Route::put('/notes/{notes}', ['as' => 'notes.update', 'uses' => 'NotesController@update']);
    Route::delete('/notes/{notes}', ['as' => 'notes.destroy', 'uses' => 'NotesController@destroy']);
    Route::resource('notes', 'NotesController', [
        'only' => ['store'],
    ]);

    // -- mediafiles: likeable | shareable | commentable (?) | tippable | purchaseable --
    //Route::post('/mediafiles/{mediafile}/doClone', ['as'=>'mediafiles.doClone', 'uses' => 'MediafilesController@doClone']);
    Route::get('/mediafiles/match', ['as'=>'mediafiles.match', 'uses' => 'MediafilesController@match']);
    Route::get('/mediafiles/disk-stats/{mediafile}', ['as'=>'mediafiles.diskStats', 'uses' => 'MediafilesController@diskStats']);
    Route::post('/mediafiles/batch-destroy', ['as'=>'mediafiles.batchDestroy', 'uses' => 'MediafilesController@batchDestroy']);
    Route::patch('/mediafiles/{mediafile}/update-tags', ['as'=>'mediafiles.updateTags', 'uses' => 'MediafilesController@updateTags']);
    Route::resource('mediafiles', 'MediafilesController', [ 'except' => [ 'create', 'edit', ] ]);

    Route::resource('diskmediafiles', 'DiskmediafilesController', [ 'only' => [ 'index', 'show', 'destroy'] ])->middleware(['role:admin|super-admin']);
    //Route::resource('diskmediafiles', 'DiskmediafilesController', [ 'only' => [ 'index', 'show', 'destroy'] ]);

    Route::get('/notifications/totalUnreadCount', ['as'=>'notifications.totalUnreadCount', 'uses' => 'NotificationsController@getTotalUnreadCount']);
    Route::resource('notifications', 'NotificationsController', [ 'only' => [ 'index', ] ]);

    /* -------------------------------- Posts ------------------------------- */
    // -- posts: likeable | shareable | commentable | tippable | purchaseable | pinnable --
    #region Posts
    Route::group(['prefix' => '/posts'], function () {
        Route::get('/match', 'PostsController@match')->name('posts.match');

        // tippable
        Route::put('/{post}/tip', 'PostsController@tip')->name('posts.tip');

        // purchaseable
        Route::put('/{post}/purchase', 'PostsController@purchase')->name('posts.purchase');

        Route::get('/{post}/index-comments', 'PostsController@indexComments')->name('posts.indexComments');
    });
    Route::apiResource('posts', 'PostsController');
    #endregion Posts

    /* ------------------------------ Sessions ------------------------------ */
    #region Sessions
    Route::resource('sessions', 'SessionsController', [ 'only' => [ 'index' ] ]);

    #endregion Sessions

    /* ------------------------------ Campaigns ------------------------------ */
    Route::get('/campaigns/active', ['as'=>'campaigns.active', 'uses' => 'CampaignsController@active']);
    Route::get('/campaigns/{user}', ['as'=>'campaigns.showActive', 'uses' => 'CampaignsController@showActive']);
    Route::post('/campaigns/stop', ['as'=>'campaigns.stop', 'uses' => 'CampaignsController@stop']);
    Route::resource('campaigns', 'CampaignsController', [ 'only' => [ 'store' ] ]);

    /* ------------------------------ Stories ------------------------------ */
    Route::get('/stories/player', ['as' => 'stories.player', 'uses' => 'SpaController@index']);
    Route::get('/stories/match', ['as'=>'stories.match', 'uses' => 'StoriesController@match']);
    Route::get('/stories/getSlides', ['as'=>'stories.getSlides', 'uses' => 'StoriesController@getSlides']);
    Route::post('/stories/markViewed', ['as'=>'stories.markViewed', 'uses' => 'StoriesController@markViewed']);
    Route::get('/stories/dashboard', [
        'middleware' => 'spaMixedRoute',
        'as' => 'stories.dashboard',
        'uses' => 'StoriesController@dashboard'
    ]);
    Route::resource('stories', 'StoriesController', [ 'except' => [ 'create', 'edit', ] ]);

    /* ------------------------------ Shareables ------------------------------ */
    Route::get('/shareables/indexFollowers', ['as' => 'shareables.indexFollowers', 'uses' => 'ShareablesController@indexFollowers']);
    Route::get('/shareables/indexFollowing', ['as' => 'shareables.indexFollowing', 'uses' => 'ShareablesController@indexFollowing']);
    Route::put('/shareables/{shareable}/clearnotes', ['as' => 'shareables.clearnotes', 'uses' => 'ShareablesController@clearnotes']);
    Route::resource('shareables', 'ShareablesController', [
        'only' => [ 'index', 'update'],
    ]);

    /* ------------------------------ Timelines ------------------------------ */
    // -- timelines: tippable | subscribeable | followable --
    #region Timelines
    Route::get('/timelines-suggested', ['as'=>'timelines.suggested', 'uses' => 'TimelinesController@suggested']); // %FIXME: refactor: use index(?)
    Route::get('/timelines/my-followed-stories', ['as'=>'timelines.myFollowedStories', 'uses' => 'TimelinesController@myFollowedStories']);
    Route::get('/timelines/public-feed', ['as'=>'timelines.publicfeed', 'uses' => 'TimelinesController@publicFeed']);
    //Route::get('/timelines/home', ['as'=>'timelines.home', 'uses' => 'TimelinesController@home']); // special case of 'show'
    Route::get('/timelines/match', ['as'=>'timelines.match', 'uses' => 'TimelinesController@match']);
    Route::get('/timelines/home/feed', ['as'=>'timelines.homefeed', 'uses' => 'TimelinesController@homefeed']);
    Route::get('/timelines/home/scheduled-feed', ['as'=>'timelines.homescheduledfeed', 'uses' => 'TimelinesController@homeScheduledfeed']);
    Route::get('/timelines/{timeline}/feed', ['as'=>'timelines.feed', 'uses' => 'TimelinesController@feed']);
    Route::get('/timelines/{timeline}/photos', ['as'=>'timelines.photos', 'uses' => 'TimelinesController@photos']); // photos feed
    Route::get('/timelines/{timeline}/videos', ['as'=>'timelines.videos', 'uses' => 'TimelinesController@videos']); // videos feed
    Route::get('/timelines/{timeline}/preview-posts', ['as'=>'timelines.previewPosts', 'uses' => 'TimelinesController@previewPosts']);
    Route::get('/timelines/{timeline}/photos-videos-count', ['as'=>'timelines.getPhotosVideosCount', 'uses' => 'TimelinesController@getPhotosVideosCount']);

    Route::put('/timelines/{timeline}/tip', ['as'=>'timelines.tip', 'uses' => 'TimelinesController@tip']);

    Route::put('/timelines/{timeline}/follow', ['as'=>'timelines.follow', 'uses' => 'TimelinesController@follow']);

    Route::put('/timelines/{timeline}/subscribe', ['as'=>'timelines.subscribe', 'uses' => 'TimelinesController@subscribe']);

    Route::put('/timelines/{timeline}/unsubscribe', ['as' => 'timelines.unsubscribe', 'uses' => 'TimelinesController@unsubscribe']);
    Route::patch('/timelines/{timeline}/set-subscription-price', ['as' => 'timelines.setSubscriptionPrice', 'uses' => 'TimelinesController@setSubscriptionPrice']);
    Route::resource('timelines', 'TimelinesController', [
        'only' => ['index', 'show'],
    ]);

    #endregion Timelines

    /* ------------------------------ Users ------------------------------ */
    // -- users: messageable --
    Route::get('/users/me', ['as' => 'users.me', 'uses' => 'UsersController@me']);
    Route::get('/users/match', ['as'=>'users.match', 'uses' => 'UsersController@match']);
    Route::post('/users/request-verify', ['as'=>'users.requestVerify', 'uses' => 'UsersController@requestVerify']);
    Route::post('/users/check-verify-status', ['as'=>'users.checkVerifyStatus', 'uses' => 'UsersController@checkVerifyStatus']);
    Route::get('/users/checkReferralCode', ['as' => 'users.checkReferralCode', 'uses' => 'UsersController@checkReferralCode']);
    Route::patch('/users/{user}/settings/enable/{group}', ['as'=>'users.enableSetting', 'uses' => 'UsersController@enableSetting']); // turn on a single update within a group
    Route::patch('/users/{user}/settings/disable/{group}', ['as'=>'users.disableSetting', 'uses' => 'UsersController@disableSetting']); // turn off a single update within a group
    Route::patch('/users/{user}/settings', ['as'=>'users.updateSettingsBatch', 'uses' => 'UsersController@updateSettingsBatch']); // batch update one (or multiple) groups at a time
    Route::patch('/users/{user}/updatePassword', ['as'=>'users.updatePassword', 'uses' => 'UsersController@updatePassword']);
    Route::post('/users/{user}/login-as-user', ['as'=>'users.loginAsUser', 'uses' => 'UsersController@loginAsUser'])->middleware(['role:admin|super-admin']);
    Route::get('/users/{user}/settings', [
        'middleware' => 'spaMixedRoute',
        'as'=>'users.showSettings',
        'uses' => 'UsersController@showSettings',
    ]);
    Route::post('/users/avatar', ['as' => 'users.updateAvatar', 'uses' => 'UsersController@updateAvatar']);
    Route::post('/users/cover', ['as' => 'users.updateCover', 'uses' => 'UsersController@updateCover']);
    Route::post('/users/send-staff-invite', ['as'=>'users.sendStaffInvite', 'uses' => 'UsersController@sendStaffInvite']);
    Route::get('/users', ['as'=>'users.index', 'uses' => 'UsersController@index'])->middleware(['role:admin|super-admin']); // describe manually to add middleware for admin
    Route::resource('users', 'UsersController', [ 'except' => [ 'index', 'create', 'edit', 'store' ] ]);

    /* ------------------------------ Vault ------------------------------ */
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
    Route::post('/vaultfolders/storeByShare', ['as'=>'vaultfolders.storeByShare', 'uses' => 'VaultfoldersController@storeByShare']);
    Route::post('/vaultfolders/{vaultfolder}/share', ['as'=>'vaultfolders.share', 'uses' => 'VaultfoldersController@share']); // ?? still used? PSG 20210701
    Route::post('/vaultfolders/{vaultfolder}/approveShare', ['as'=>'vaultfolders.approveShare', 'uses' => 'VaultfoldersController@approveShare']);
    Route::post('/vaultfolders/{vaultfolder}/declineShare', ['as'=>'vaultfolders.declineShare', 'uses' => 'VaultfoldersController@declineShare']);
    Route::get('/vaultfolders/uploads/{type}', 'VaultfoldersController@uploadsFolder')
        ->name('vaultfolders.uploads-folder');
    Route::resource('vaultfolders', 'VaultfoldersController', [ ]);

    Route::get('/verifyrequests/{vr}/check-status', ['as'=>'verifyrequests.checkStatus', 'uses' => 'VerifyrequestsController@checkStatus'])->middleware(['role:admin|super-admin']);
    Route::resource('verifyrequests', 'VerifyrequestsController', [ 'only' => [ 'index', 'show', ] ])->middleware(['role:admin|super-admin']);

    // -- misc --
    Route::post('update-last-seen', 'UsersController@updateLastSeen')->name('update-user-status');

});

/* ------------------------------ Financial Namespace ------------------------------ */
// %NOTE: currently these are limited to super-admin roles
Route::group(['middleware' => ['auth', 'role:admin|super-admin'], 'as'=>'financial.', 'prefix'=>'financial', 'namespace'=>'Financial'], function () {

    Route::resource('accounts', 'AccountsController', [ 
        'only' => [ 'index', 'show' ],
    ]);

    Route::get('/summary/transactions', 'TransactionsController@summary')->name('transactions.summary');
    Route::resource('transactions', 'TransactionsController', [ 
        'only' => [ 'index', 'show' ],
    ]);
});

/*
|--------------------------------------------------------------------------
| UsernameRules 
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => '/username'], function() {
    Route::match(['get', 'post'], '/check/{username?}', 'UsernameRulesController@checkUsername')->name('usernameRules.check');

    // Admin Crud API //
    Route::group(['middleware' => ['auth', 'role:admin|super-admin']], function() {
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

/*
  Staff
 */
Route::get('/staff-members/managers', ['as'=>'staff.indexManagers', 'uses' => 'StaffController@indexManagers']);
Route::get('/staff-members/staff', ['as'=>'staff.indexStaffMembers', 'uses' => 'StaffController@indexStaffMembers']);
Route::delete('/staff-members/{id}', ['as'=>'staff.remove', 'uses' => 'StaffController@remove']);
Route::post('/staff-members/invitations/accept', ['as'=>'staff.acceptInvite', 'uses' => 'StaffController@acceptInvite']);
Route::patch('/staff-members/{id}/status', ['as'=>'staff.changestatus', 'uses' => 'StaffController@changeStatus']);
Route::get('/staff-members/permissions', ['as'=>'staff.permissions', 'uses' => 'StaffController@listPermissions']);
Route::get('/staff-members/managers/{id}', ['as'=>'staff.getManager', 'uses' => 'StaffController@getManager']);
Route::patch('/staff-members/managers/{id}/settings', ['as'=>'staff.updateManagerSettings', 'uses' => 'StaffController@updateManagerSettings']);

// 
/**
 * Single Page application catch all undefined routes
 * Laravel router will first try to match static resources, then specific routes, then finally this.
 */
Route::get('/n0g1cg9sbx/{any}', 'SpaController@admin')->name('spa.admin')->where('any', '.*');
Route::get('/{any}', 'SpaController@index')->name('spa.index')->where('any', '.*');
