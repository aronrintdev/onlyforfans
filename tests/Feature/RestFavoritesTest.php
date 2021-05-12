<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Favorite;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\User;

class RestFavoritesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group favorites
     *  @group regression
     */
    public function test_can_list_favorites()
    {
        $favoriter = User::has('favorites','>=',1)->firstOrFail();
        $expectedCount = Favorite::where('user_id', $favoriter->id)->count();

        $response = $this->actingAs($favoriter)->ajaxJSON('GET', route('favorites.index'), [
            'user_id' => $favoriter->id,
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
        collect($content->data)->each( function($c) use(&$favoriter) { // all belong to owner
            $this->assertEquals($favoriter->id, $c->user_id);
        });
    }

    /**
     *  @group favorites
     *  @group regression
     */
    public function test_can_list_filtered_favorites()
    {
        $favoriter = User::whereHas('favorites', function($q1) {
            $q1->where('favoritable_type', 'mediafiles');
        })->firstOrFail();
        $expectedCount = Favorite::where('user_id', $favoriter->id)
            ->where('favoritable_type', 'mediafiles')
            ->count();

        $response = $this->actingAs($favoriter)->ajaxJSON('GET', route('favorites.index'), [
            'favoritable_type' => 'mediafiles',
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
        collect($content->data)->each( function($c) use(&$favoriter) { // all belong to owner
            $this->assertEquals($favoriter->id, $c->user_id);
        });

        $returnedCount = collect($content->data)->reduce( function($acc, $item) use(&$favoriter) {
            if ( ($item->user_id === $favoriter->id) && ($item->favoritable_type === 'mediafiles') ) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(count($content->data), $returnedCount); 
    }

    /**
     *  @group favorites
     *  @group regression
     */
    public function test_can_add_favorite()
    {
        $post = Post::first();
        $poster = $post->user;
        $favoriter = User::doesntHave('favorites')->where('id', '<>', $poster->id)->first();
        $response = $this->actingAs($favoriter)->ajaxJSON('POST', route('favorites.store'), [
            'favoritable_id' => $post->id,
            'favoritable_type' => 'posts',
        ]);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $favorite = $content->data;

        $response = $this->actingAs($favoriter)->ajaxJSON('GET', route('favorites.show', $favorite->id));
        $response->assertStatus(200);
    }

    /**
     *  @group favorites
     *  @group regression
     */
    // should return only favorites for which session user is the owner
    public function test_nonadmin_can_not_list_general_favorites()
    {
        $favoriter = User::doesntHave('favorites')->firstOrFail();
        $response = $this->actingAs($favoriter)->ajaxJSON('GET', route('favorites.index'));
        $response->assertStatus(200); // instead of 403, we just get our own favorites back (filter is ignored)

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertEquals(0, count($content->data));
    }

    /**
     *  @group favorites
     *  @group regression
     */
    public function test_can_view_own_favorite()
    {
        $favoriter = User::has('favorites','>=',1)->firstOrFail();
        $favorite = Favorite::where('user_id', $favoriter->id)->first();
        $response = $this->actingAs($favoriter)->ajaxJSON('GET', route('favorites.show', $favorite->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertObjectHasAttribute('favoritable_type', $content->data);
        $this->assertNotNull($content->data->favoritable_type);
        $this->assertObjectHasAttribute('favoritable_id', $content->data);
        $this->assertNotNull($content->data->favoritable_id);
    }

    /**
     *  @group favorites
     *  @group regression
     */
    public function test_can_delete_own_favorite()
    {
        $favoriter = User::has('favorites', '>', 1)->first();
        $favorite = Favorite::where('user_id', $favoriter->id)->first();
        $response = $this->actingAs($favoriter)->ajaxJSON('DELETE', route('favorites.destroy', $favorite->id));
        $response->assertStatus(200);
        $response = $this->actingAs($favoriter)->ajaxJSON('GET', route('favorites.show', $favorite->id));
        $response->assertStatus(404);
    }

    /**
     *  @group favorites
     *  @group regression
     */
    public function test_can_remove_own_favorite()
    {
        $favoriter = User::has('favorites', '>', 1)->first();
        $favorite = Favorite::where('user_id', $favoriter->id)->first();
        $response = $this->actingAs($favoriter)->ajaxJSON('POST', route('favorites.remove'), [
            'favoritable_id' => $favorite->favoritable_id,
            'favoritable_type' => $favorite->favoritable_type,
        ]);
        $response->assertStatus(200);
        $response = $this->actingAs($favoriter)->ajaxJSON('GET', route('favorites.show', $favorite->id));
        $response->assertStatus(404);
    }

    /**
     *  @group favorites
     *  @group regression
     */
    public function test_can_not_delete_nonowned_favorite()
    {
        $favoriter = User::has('favorites', '>', 1)->first();
        $favorite = Favorite::where('user_id', $favoriter->id)->first();
        $nonowner = User::where('id', '<>', $favoriter->id)->firstOrFail();
        $response = $this->actingAs($nonowner)->ajaxJSON('DELETE', route('favorites.destroy', $favorite->id));
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

