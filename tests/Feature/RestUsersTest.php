<?php
namespace Tests\Feature;

use Auth;
use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Timeline;
use App\Models\User;

class RestUsersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group users
     *  @group regression
     */
    public function test_admin_can_list_users()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;

        $admin = User::where('id', '<>', $creator->id)->first();
        $admin->assignRole('super-admin'); // upgrade to admin!
        $admin->refresh();

        $expectedCount = User::count();
        $response = $this->actingAs($admin)->ajaxJSON('GET', route('users.index'), [ ]);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertEquals($expectedCount, count($content->data));
        $this->assertObjectHasAttribute('username', $content->data[0]);
    }

    /**
     *  @group users
     *  @group regression
     */
    public function test_user_can_view_settings()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.showSettings', $creator->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertObjectHasAttribute('user_id', $content->data);
        $this->assertObjectHasAttribute('about', $content->data);
    }

    /**
     *  @group users
     *  @group regression
     */
    public function test_user_can_login()
    {
        $user = User::first();
        $payload = [ 'email' => $user->email, 'password' => 'foo-123' ];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertStatus(200);
    }

    /**
     *  @group users
     *  @group regression
     */
    public function test_user_cant_login_with_wrong_credientials()
    {
        $user = User::first();
        $payload = [ 'email' => $user->email, 'password' => 'blahblah' ];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertUnauthorized(); // 401
    }

    /**
     *  @group users
     *  @group regression
     */
    public function test_user_can_change_password()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;

        $oldPassword = 'foo-123'; // yes, hardcoded from the seeders
        $newPassword = 'bar-123';
        $payload = [ 'oldPassword' => $oldPassword, 'newPassword' => $newPassword ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.updatePassword', $creator->id), $payload);
        $response->assertStatus(200);

        Auth::logout();

        // Test login with old password
        $payload = [ 'email' => $creator->email, 'password' => $oldPassword ];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertUnauthorized(); // 401

        // Test login with new password
        $payload = [ 'email' => $creator->email, 'password' => $newPassword ];
        $response = $this->ajaxJSON('POST', '/login', $payload);
        $response->assertStatus(200);
    }

    /**
     *  @group users
     *  @group regression
     *  @group here
     */
    public function test_user_can_get_session()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.me'));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'uiFlags' => [ 'isAdmin' ],
            'timeline' => [ 
                //'followers',
                //'following',
                //'earnings',
                'userstats' => ['post_count', 'like_count', 'follower_count', 'following_count', 'subscribed_count', 'earnings'],
            ],
            'session_user' => [ 'email', 'name', 'avatar', 'cover', 'about', 'roles', ],
        ]);
        $this->assertObjectNotHasAttribute('timeline', $content->session_user);
        $this->assertObjectNotHasAttribute('settings', $content->session_user);
    }

    /**
     *  @group users
     *  @group regression
     */
    public function test_admin_can_matchsearch_users()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $admin = User::where('id', '<>', $creator->id)->first();
        $admin->assignRole('super-admin'); // upgrade to admin!
        $admin->refresh();
        $payload = [
            'term' => $creator->email,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.match', $admin->id), $payload);
        $response->assertStatus(200);
    }


    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

