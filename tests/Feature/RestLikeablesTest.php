<?php
namespace Tests\Feature;

use Exception;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;

//use App\Enums\PostTypeEnum;
//use App\Enums\PaymentTypeEnum;
use App\Models\Story;
use App\Models\Comment;
use App\Models\Likeable;
use App\Models\Timeline;
use App\Models\Mediafile;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;

class RestLikeablesTest extends TestCase
{
    use WithFaker;

    /**
     *  @group likeables
     *  @group regression
     */
    public function test_owner_can_list_likeables()
    {
        $post = Post::has('likes','>=',2)->firstOrFail(); // base on a post for now
        $creator = $post->user;
        $timeline = $creator->timeline;

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('likeables.index'), [
            //'user_id' => $creator->id,
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        //dd($content);
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertObjectHasAttribute('likeable_type', $content->data[0]);
        $this->assertObjectHasAttribute('likeable_id', $content->data[0]);
        $this->assertObjectHasAttribute('liker_id', $content->data[0]);

        // All resources returned are owned by creator
        $ownedByCreator = collect($content->data)->reduce( function($acc, $item) use(&$creator) {
            switch ( $item->likeable_type ) {
            case 'posts':
                $likeable = Post::find($item->likeable_id);
                break;
            case 'comments':
                $likeable = Comment::find($item->likeable_id);
                break;
            case 'stories':
                $likeable = Story::find($item->likeable_id);
                break;
            case 'mediafiles':
                $likeable = Mediafile::find($item->likeable_id);
                break;
            default:
                throw new Exception('Unknown likeable_type: '.$item->likeable_type);
            }
            if ( $likeable->getPrimaryOwner()->id === $creator->id ) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(count($content->data), $ownedByCreator); 
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

