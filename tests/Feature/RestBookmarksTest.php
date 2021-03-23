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
    public function test_can_list_bookmarks()
    {
        $bookmarker = User::has('bookmarks','>=',1)->firstOrFail();
        $expectedCount = Bookmark::where('user_id', $bookmarker->id)->count();

        $response = $this->actingAs($bookmarker)->ajaxJSON('GET', route('bookmarks.index'), [
            'filters' => [
                'user_id' => $bookmarker->id,
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
        collect($content->data)->each( function($c) use(&$bookmarker) { // all belong to owner
            $this->assertEquals($bookmarker->id, $c->user_id);
        });
    }

    /**
     *  @group bookmarks
     *  @group regression
     *  @group here03
     */
    public function test_can_add_bookmark()
    {
        $post = Post::first();
        $poster = $post->user;
        $bookmarker = User::doesntHave('bookmarks')->where('id', '<>', $poster->id)->first();
        $response = $this->actingAs($bookmarker)->ajaxJSON('POST', route('bookmarks.store'), [
            'bookmarkable_id' => $post->id,
            'bookmarkable_type' => 'posts',
        ]);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $bookmark = $content->data;

        $response = $this->actingAs($bookmarker)->ajaxJSON('GET', route('bookmarks.show', $bookmark->id));
        $response->assertStatus(200);
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    // should return only bookmarks for which session user is the owner
    public function test_nonadmin_can_not_list_general_bookmarks()
    {
        $bookmarker = User::doesntHave('bookmarks')->firstOrFail();
        $response = $this->actingAs($bookmarker)->ajaxJSON('GET', route('bookmarks.index'));
        $response->assertStatus(200); // instead of 403, we just get our own bookmarks back (filter is ignored)

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertEquals(0, count($content->data));
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    public function test_can_view_own_bookmark()
    {
        $bookmarker = User::has('bookmarks','>=',1)->firstOrFail();
        $bookmark = Bookmark::where('user_id', $bookmarker->id)->first();
        $response = $this->actingAs($bookmarker)->ajaxJSON('GET', route('bookmarks.show', $bookmark->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertObjectHasAttribute('bookmarkable_type', $content->data);
        $this->assertNotNull($content->data->bookmarkable_type);
        $this->assertObjectHasAttribute('bookmarkable_id', $content->data);
        $this->assertNotNull($content->data->bookmarkable_id);
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    public function test_can_delete_own_bookmark()
    {
        $bookmarker = User::has('bookmarks', '>', 1)->first();
        $bookmark = Bookmark::where('user_id', $bookmarker->id)->first();
        $response = $this->actingAs($bookmarker)->ajaxJSON('DELETE', route('bookmarks.destroy', $bookmark->id));
        $response->assertStatus(200);
        $response = $this->actingAs($bookmarker)->ajaxJSON('GET', route('bookmarks.show', $bookmark->id));
        $response->assertStatus(404);
    }

    /**
     *  @group bookmarks
     *  @group regression
     *  @group here03
     */
    public function test_can_remove_own_bookmark()
    {
        $bookmarker = User::has('bookmarks', '>', 1)->first();
        $bookmark = Bookmark::where('user_id', $bookmarker->id)->first();
        $response = $this->actingAs($bookmarker)->ajaxJSON('POST', route('bookmarks.remove'), [
            'bookmarkable_id' => $bookmark->bookmarkable_id,
            'bookmarkable_type' => $bookmark->bookmarkable_type,
        ]);
        $response->assertStatus(200);
        $response = $this->actingAs($bookmarker)->ajaxJSON('GET', route('bookmarks.show', $bookmark->id));
        $response->assertStatus(404);
    }

    /**
     *  @group bookmarks
     *  @group regression
     */
    public function test_can_not_delete_nonowned_bookmark()
    {
        $bookmarker = User::has('bookmarks', '>', 1)->first();
        $bookmark = Bookmark::where('user_id', $bookmarker->id)->first();
        $nonowner = User::where('id', '<>', $bookmarker->id)->firstOrFail();
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

