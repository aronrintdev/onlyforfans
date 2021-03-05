<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;

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
        $response = $this->actingAs($admin)->ajaxJSON('GET', route('users.index'), [
        ]);
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
    }

    /**
     *  @group users
     *  @group regression
     */
    public function test_user_can_change_password()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $payload = [
            'oldPassword' => 'foo-123',
            'newPassword' => 'bar-123',
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('users.updatePassword', $creator->id), $payload);
        $response->assertStatus(200);
    }

    /**
     *  @group users
     *  @group regression
     */
    public function test_user_can_get_session()
    {
        $timeline = Timeline::has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.me'));
        $response->assertStatus(200);
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
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('users.showSettings'), $payload);
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

