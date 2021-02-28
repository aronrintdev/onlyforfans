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

Route::get('linkedin', 'Auth\RegisterController@linkedinRedirect');
Route::get('account/linkedin', 'Auth\RegisterController@linkedin');

/* ---------------------------------- Login --------------------------------- */
// Route::get('/login', 'Auth\LoginController@getLogin');
Route::post('/login', 'Auth\LoginController@login');
// Route::get('/login2', 'Auth\LoginController@login');

/* -------------------------------- Register -------------------------------- */
// Route::get('/register', 'Auth\RegisterController@register')->name('auth.register');
Route::post('/register', 'Auth\RegisterController@registerUser');
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

    // -- comments: likeable --
    Route::patch('/comments/{comment}/like', ['as'=>'comments.toggleLike', 'uses' => 'CommentsController@toggleLike']);
    Route::get('/comments/match', ['as'=>'comments.match', 'uses' => 'CommentsController@match']);
    Route::resource('comments', 'CommentsController', [ 'except' => ['create','edit'] ]);

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
    Route::put('/likeables/{likee}', ['as'=>'likeables.update', 'uses' => 'LikeablesController@update']); // like
    Route::delete('/likeables/{likee}', ['as'=>'likeables.destroy', 'uses' => 'LikeablesController@destroy']); // unlike
    Route::resource('likeables', 'LikeablesController', [
        'only' => [ 'index' ],
    ]);

    // -- mediafiles: likeable | shareable | commentable (?) | tippable | purchaseable --
    //Route::post('/mediafiles/{mediafile}/doClone', ['as'=>'mediafiles.doClone', 'uses' => 'MediafilesController@doClone']);
    Route::get('/mediafiles/match', ['as'=>'mediafiles.match', 'uses' => 'MediafilesController@match']);
    Route::resource('mediafiles', 'MediafilesController', [ 'except' => [ 'create', 'edit', ] ]);

    // -- posts: likeable | shareable | commentable | tippable | purchaseable | pinnable --
    Route::get('/posts/match', ['as'=>'posts.match', 'uses' => 'PostsController@match']);
    Route::put('/posts/{post}/tip', ['as'=>'posts.tip', 'uses' => 'PostsController@tip']);
    Route::put('/posts/{post}/purchase', ['as'=>'posts.purchase', 'uses' => 'PostsController@purchase']);
    Route::patch('/posts/{post}/attachMediafile/{mediafile}', ['as'=>'posts.attachMediafile', 'uses' => 'PostsController@attachMediafile']);
    Route::get('/posts/{post}/index-comments', ['as'=>'posts.indexComments', 'uses' => 'PostsController@indexComments']);
    Route::resource('posts', 'PostsController', [ 
        'except' => [ 'create', 'edit', ],
    ]);

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
    Route::resource('shareables', 'ShareablesController', [
        'only' => [ 'index', ],
    ]);

    // -- timelines: tippable | subscribeable | followable --
    Route::get('/timelines-suggested', ['as'=>'timelines.suggested', 'uses' => 'TimelinesController@suggested']); // %FIXME: refactor: use index(?)
    //Route::get('/timelines/home', ['as'=>'timelines.home', 'uses' => 'TimelinesController@home']); // special case of 'show'
    Route::get('/timelines/match', ['as'=>'timelines.match', 'uses' => 'TimelinesController@match']);
    Route::get('/timelines/home/feed', ['as'=>'timelines.homefeed', 'uses' => 'TimelinesController@homefeed']);
    Route::get('/timelines/{timeline}/feed', ['as'=>'timelines.feed', 'uses' => 'TimelinesController@feed']);
    Route::put('/timelines/{timeline}/tip', ['as'=>'timelines.tip', 'uses' => 'TimelinesController@tip']);
    Route::put('/timelines/{timeline}/follow', ['as'=>'timelines.follow', 'uses' => 'TimelinesController@follow']);
    Route::put('/timelines/{timeline}/subscribe', ['as'=>'timelines.subscribe', 'uses' => 'TimelinesController@subscribe']);
    Route::resource('timelines', 'TimelinesController', [
        'only' => ['index', 'show'],
    ]);

    // -- users: messageable --
    //Route::get('/users-suggested', ['as'=>'users.suggested', 'uses' => 'UsersController@suggested']);
    Route::get('/users/me', ['as' => 'users.me', 'uses' => 'UsersController@me']);
    Route::get('/users/match', ['as'=>'users.match', 'uses' => 'UsersController@match']);
    Route::resource('users', 'UsersController', [ 'except' => [ 'create', 'edit', ] ]);

    // -- vaults:  --
    Route::get('/my-vault', [
        'middleware' => 'spaMixedRoute',
        'as' => 'vault.dashboard',
        'uses' => 'VaultsController@dashboard'
    ]);
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


/*
|--------------------------------------------------------------------------
| UsernameRules 
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => '/username'], function() {
    Route::match(['get', 'post'], '/check/{username?}', 'UsernameRulesController@checkUsername')->name('usernameRules.index');

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
| Webhook
|--------------------------------------------------------------------------
*/

Route::post('hook/receive', 'WebhooksController@receive')->name('webhook.receive');

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

/**
 * Single Page application catch all undefined routes
 * Laravel router will first try to match static resources, then specific routes, then finally this.
 */
Route::get('/{any}', 'SpaController@index')->where('any', '.*');
