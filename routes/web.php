<?php
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Auth 
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web']], function () {
    Auth::routes();
});

Route::get('facebook', 'Auth\RegisterController@facebookRedirect'); // auth redirect
Route::get('account/facebook', 'Auth\RegisterController@facebook'); // return url (?)

Route::get('google', 'Auth\RegisterController@googleRedirect');
Route::get('account/google', 'Auth\RegisterController@google');

Route::get('twitter', 'Auth\RegisterController@twitterRedirect');
Route::get('account/twitter', 'Auth\RegisterController@twitter');

Route::get('linkedin', 'Auth\RegisterController@linkedinRedirect');
Route::get('account/linkedin', 'Auth\RegisterController@linkedin');

// Login
Route::get('/login', 'Auth\LoginController@getLogin');
// Route::post('/login', 'Auth\LoginController@login');
// Route::get('/login2', 'Auth\LoginController@login');

// Register
Route::get('/register', 'Auth\RegisterController@register')->name('auth.register');
Route::post('/register', 'Auth\RegisterController@registerUser');
Route::get('email/verify', 'Auth\RegisterController@verifyEmail');

//main project register
// Route::get('/main-register', 'Auth\RegisterController@mainProjectRegister');
Route::post('/main-login', 'Auth\LoginController@mainProjectLogin');
// Route::get('/main-user-update', 'Auth\RegisterController@mainUserUpdate');

/*
|--------------------------------------------------------------------------
| Resources
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth']], function () {

    // -- comments: likeable --
    //Route::patch('/comments/{comment}/like', ['as'=>'comments.toggleLike', 'uses' => 'CommentsController@toggleLike']);
    Route::get('/comments/match', ['as'=>'comments.match', 'uses' => 'CommentsController@match']);
    Route::resource('comments', 'CommentsController', [ 'except' => ['create','edit'] ]);

    // -- fanledgers:  --
    //  ~ 'store' is the implmentation for most/all 'tippables' (???)
    Route::resource('fanledgers', 'FanledgersController', [
        'only' => [ 'index', 'show', 'store' ],
    ]);

    // -- likeables:  --
    // ~ PUT /api/v1/resource/:id/likes/:userid
    // ~ DELETE /api/v1/resource/:id/likes/:userid
    // see: https://stackoverflow.com/questions/5665893/the-rest-way-to-check-uncheck-like-unlike-favorite-unfavorite-a-resource
    Route::put('/likeables/{likee}', ['as'=>'likeables.update', 'uses' => 'LikeablesController@update']); // %FIXME: refactor to make consistent
    Route::resource('likeables', 'LikeablesController', [
        'only' => [ 'index', 'update', 'destroy' ],
    ]);

    // -- mediafiles: likeable | shareable | commentable (?) | tippable | purchaseable --
    Route::get('/mediafiles/match', ['as'=>'mediafiles.match', 'uses' => 'MediafilesController@match']);
    Route::resource('mediafiles', 'MediafilesController', [ ]);

    // -- posts: likeable | shareable | commentable | tippable | purchaseable | pinnable --
    Route::get('/posts/match', ['as'=>'posts.match', 'uses' => 'PostsController@match']);
    Route::resource('posts', 'PostsController', [ ]);

    // -- stories:  --
    Route::get('/stories/player', ['as'=>'stories.player', 'uses' => 'StoriesController@player']);
    Route::get('/stories/match', ['as'=>'stories.match', 'uses' => 'StoriesController@match']);
    Route::resource('stories', 'StoriesController', [ ]);

    // -- shareables:  --
    //   ~  implements push-share(?), followable, subscribeable
    Route::resource('shareables', 'ShareablesController', [
        'only' => [ 'index', 'update', 'destroy' ],
    ]);

    // -- timelines: tippable | subscribeable | followable --
    Route::get('/timelines-suggested', ['as'=>'timelines.suggested', 'uses' => 'TimelinesController@suggested']); // %FIXME: refactor: use index(?)
    Route::get('/timelines/home', ['as'=>'timelines.home', 'uses' => 'TimelinesController@home']); // special case of 'show'
    Route::get('/timelines/{timeline}/feeditems', ['as'=>'timelines.feeditems', 'uses' => 'TimelinesController@feeditems']);
    Route::get('/timelines/match', ['as'=>'timelines.match', 'uses' => 'TimelinesController@match']);
    Route::resource('timelines', 'TimelinesController', [
        'only' => [ 'index', 'show' ],
    ]);

    // -- users: messageable --
    //Route::get('/users-suggested', ['as'=>'users.suggested', 'uses' => 'UsersController@suggested']);
    Route::get('/users/me', ['as'=>'users.me', 'uses' => 'UsersController@me']);
    Route::get('/users/match', ['as'=>'users.match', 'uses' => 'UsersController@match']);
    Route::resource('users', 'UsersController', [ ]);

    // -- vaults:  --
    Route::get('/my-vault', ['as'=>'vault.dashboard', 'uses' => 'VaultsController@dashboard']);
    Route::patch('/vaults/{vault}/update-shares', ['as'=>'vaults.updateShares', 'uses' => 'VaultsController@updateShares']); // %FIXME: refactor to make consistent
    Route::resource('vaults', 'VaultsController', [
        'only' => [ 'index', 'show' ],
    ]);

    // -- vaultfolders: shareable | purchaseable --
    Route::get('/vaultfolders/match', ['as'=>'vaultfolders.match', 'uses' => 'VaultfoldersController@match']);
    Route::resource('vaultfolders', 'VaultfoldersController', [ ]);

    // -- misc --
    Route::post('update-last-seen', 'UserController@updateLastSeen')->name('update-user-status');
    /*
    Route::get('/saved/dashboard', ['as'=>'saved.dashboard', 'uses' => 'SaveditemsController@dashboard']);
    Route::resource('saved', 'SaveditemsController', [
        'only' => [ 'index', 'show', 'store' ],
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
| Webhok 
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
Route::get('faq', 'PageController@faq');
Route::get('support', 'PageController@support');
Route::get('terms-of-use', 'PageController@termsOfUse');
Route::get('privacy-policy', 'PageController@privacyPolicy');
Route::get('dmca', 'PageController@dmca');
Route::get('usc2257', 'PageController@usc2257');
Route::get('legal', 'PageController@legal');
Route::get('blog', 'PageController@blog');



/*
|--------------------------------------------------------------------------
| Misc
|--------------------------------------------------------------------------
*/

Route::get('/home', 'HomeController@index');
