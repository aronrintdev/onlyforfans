<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;
use Ramsey\Uuid\Uuid;
use App\Post;
//use App\Vault;
use App\Libs\UserMgr;
use App\Enums\PostTypeEnum;

class SubscribeableTest extends TestCase
{

    use WithFaker;

    private $_deleteList;
    private static $_persist = false;

    /**
     * @group subdev
     */
    public function test_can_follow_timeline()
    {

        $follower = $this->follower;
        $creator = $this->creator;
        $response = UserMgr::toggleFollow($follower, $creator->timeline, ['is_subscribe'=>false]);
        $follower->refresh();
        $creator->refresh();

        // --

        $shareables = DB::table('shareables')
            ->where('sharee_id', $follower->id)
            ->where('shareable_id', $creator->timeline->id)
            ->where('shareable_type', 'timelines')
            ->get();
        $this->assertEquals(1, count($shareables) );
        $this->assertSame('default', $shareables[0]->access_level );

        $this->assertNotNull($follower->followedtimelines);
        $this->assertGreaterThan(0, $follower->followedtimelines->count());
        $this->assertSame($creator->timeline->id, $follower->followedtimelines[0]->id);

        $this->assertNotNull($creator->timeline->followers);
        $this->assertGreaterThan(0, $creator->timeline->followers->count());
        $this->assertSame($follower->id, $creator->timeline->followers[0]->id);

        //$this->assertInstanceOf(\App\User::class, $mediafile->sharees[0]);
    }


    protected function setUp() : void {
        parent::setUp();
        $this->_deleteList = collect();

        $this->follower = factory(\App\User::class)->create();
        $this->_deleteList->push($this->follower);

        $this->creator = factory(\App\User::class)->create();
        $this->_deleteList->push($this->creator);

        // Create some posts for the 'seller user'
        $post = factory(Post::class,3)->create([
            'user_id'      => $this->creator->id,
            'timeline_id'  => $this->creator->timeline->id,
            'type'         => PostTypeEnum::FREE,
        ]);

        $post = factory(Post::class,3)->create([
            'user_id'      => $this->creator->id,
            'timeline_id'  => $this->creator->timeline->id,
            'type'         => PostTypeEnum::PRICED,
            'price'        => $this->faker->randomFloat(2, 1, 300),
        ]);

        $post = factory(Post::class,3)->create([
            'user_id'      => $this->creator->id,
            'timeline_id'  => $this->creator->timeline->id,
            'type'         => PostTypeEnum::SUBSCRIBER,
        ]);
    }

    protected function tearDown() : void {
        if ( !self::$_persist ) {
            while ( $this->_deleteList->count() > 0 ) {
                $obj = $this->_deleteList->pop();
                if ( $obj instanceof Vault ) {
                     $obj->vaultfolders()->delete();
                }
                if ( $obj instanceof Mediafile ) {
                     $obj->sharees()->detach();
                }
                if ( $obj instanceof \App\User ) {
                     $obj->followedtimelines()->detach();
                     $obj->ledgersales->each( function($o) { $o->forceDelete(); } );
                     $obj->ledgerpurchases->each( function($o) { $o->forceDelete(); } );
                     $obj->posts->each( function($o) { $o->forceDelete(); } );
                     $obj->timeline->followers()->detach();
                     $obj->timeline->forceDelete();
                }
                $obj->delete();
            }
        }
        parent::tearDown();
    }

}
