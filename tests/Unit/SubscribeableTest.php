<?php
namespace Tests\Unit;

use DB;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Libs\UserMgr;
use App\Models\Vault;
use Ramsey\Uuid\Uuid;
use App\Models\MediaFile;
use App\Enums\PostTypeEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        $this->assertNotNull($follower->followedTimelines);
        $this->assertGreaterThan(0, $follower->followedTimelines->count());
        $this->assertSame($creator->timeline->id, $follower->followedTimelines[0]->id);

        $this->assertNotNull($creator->timeline->followers);
        $this->assertGreaterThan(0, $creator->timeline->followers->count());
        $this->assertSame($follower->id, $creator->timeline->followers[0]->id);

        //$this->assertInstanceOf(User::class, $mediaFile->sharees[0]);
    }


    protected function setUp() : void {
        parent::setUp();
        $this->_deleteList = collect();

        $this->follower = factory(User::class)->create();
        $this->_deleteList->push($this->follower);

        $this->creator = factory(User::class)->create();
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
                     $obj->vaultFolders()->delete();
                }
                if ( $obj instanceof MediaFile ) {
                     $obj->sharees()->detach();
                }
                if ( $obj instanceof User ) {
                     $obj->followedTimelines()->detach();
                     $obj->ledgerSales->each( function($o) { $o->forceDelete(); } );
                     $obj->ledgerPurchases->each( function($o) { $o->forceDelete(); } );
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
