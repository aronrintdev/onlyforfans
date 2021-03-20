<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Bookmark;
use App\Models\Post;
use App\Models\User;

class RestBookmarksTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group bookmarks
     *  @group regression
     */
    public function test_owner_can_list_bookmarks()
    {
        $owner = User::has('bookmarks','>=',1)->firstOrFail();
        $expectedCount = Bookmark::where('owner', $owner->id)->count();

        $response = $this->actingAs($owner)->ajaxJSON('GET', route('bookmarks.index'), [
            'filters' => [
                'user_id' => $owner->id,
            ],
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertEquals($expectedCount, count($content->data));
        collect($content->data)->each( function($c) use(&$owner) { // all belong to owner
            $this->assertEquals($owner->id, $c->user_id);
        });
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    // should return only bookmarks for which session user is the owner
    public function test_nonadmin_can_not_list_general_bookmarks()
    {
        $owner = User::doesntHave('bookmarks')->firstOrFail();
        $response = $this->actingAs($owner)->ajaxJSON('GET', route('bookmarks.index'));
        $response->assertStatus(200); // instead of 403, we just get our own bookmarks back (filter is ignored)

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertEquals(0, count($content->data));
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    public function test_owner_can_view_own_bookmark()
    {
        $owner = User::has('bookmarks','>=',1)->firstOrFail();
        $bookmark = Bookmark::where('user_id', $owner->id)->first();
        $response = $this->actingAs($owner)->ajaxJSON('GET', route('bookmarks.show', $bookmark->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->bookmark);
        $this->assertObjectHasAttribute('bookmarkabke_type', $content->bookmark);
        $this->assertNotNull($content->bookmark->bookmarkable_type);
        $this->assertObjectHasAttribute('bookmarkabke_id', $content->bookmark);
        $this->assertNotNull($content->bookmark->bookmarkable_id);
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    public function test_can_delete_own_bookmark()
    {
        $owner = User::has('bookmarks', '>', 1)->first();
        $bookmark = Bookmark::where('user_id', $owner->id)->first();
        $response = $this->actingAs($owner)->ajaxJSON('DELETE', route('bookmarks.destroy', $bookmark->id));
        $response->assertStatus(200);
        $response = $this->actingAs($owner)->ajaxJSON('GET', route('bookmarks.show', $bookmark->id));
        $response->assertStatus(404);
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    public function test_can_not_delete_nonowned_bookmark()
    {
        $owner = User::has('bookmarks', '>', 1)->first();
        $bookmark = Bookmark::where('user_id', $owner->id)->first();
        $nonowner = User::where('id', '<>', $owner->id)->firstOrFail();
        $response = $this->actingAs($nonowner)->ajaxJSON('DELETE', route('bookmarks.destroy', $bookmark->id));
        $response->assertStatus(403);
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

