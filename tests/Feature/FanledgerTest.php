<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\User;
use App\Post;

class FanledgerTest extends TestCase
{
    use WithFaker;

    private $_deleteList;
    private static $_persist = false;

    //private $admin;
    private $buyerUser = null; // ie session user
    private $sellerUser = null;

    /**
     *  @group fldev
     */
    public function test_can_send_tip_on_post()
    {
        $buyerUser = $this->buyerUser;
        $sellerUser = $this->sellerUser;
        $post = Post::where('timeline_id', $sellerUser->timeline->id)->where('type', PostTypeEnum::FREE)->first();
        $payload = [
            'amount' => $this->faker->randomFloat(2, 1, 99),
        ];
        $response = $this->actingAs($buyerUser)->ajaxJSON('POST', route('posts.tip', $post->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $postR = $content->post;
        $this->assertEquals($post->id, $postR->id);

        $post->refresh();
        $buyerUser->refresh();
        $sellerUser->refresh();

        $this->assertEquals( 1, $post->ledgersales->count() );
        $this->assertEquals( PaymentTypeEnum::TIP, $post->ledgersales[0]->fltype );
        $this->assertEquals( 'posts', $post->ledgersales[0]->purchaseable_type );

        $this->assertEquals( 1, $buyerUser->ledgerpurchases->count() );
        $this->assertEquals( $post->ledgersales[0]->id, $buyerUser->ledgerpurchases[0]->id );

        $fl = $post->ledgersales()->where('purchaser_id', $buyerUser->id)->first();
        $this->assertEquals( $post->id, $fl->purchaseable_id );
        $this->assertEquals( 1, $fl->qty );
        $this->assertEquals( $payload['amount'], $fl->total_amount ); // no taxes applied
    }

    /**
     *  @group fldev
     */
    public function test_can_send_tip_to_user()
    {
        $buyerUser = $this->buyerUser; // tipper
        $sellerUser = $this->sellerUser; // tippee
        $payload = [ ];
        $response = $this->actingAs($buyerUser)->ajaxJSON('POST', route('users.tip', $sellerUser->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $buyerUser->refresh();
        $sellerUser->refresh();

        $this->assertEquals( 1, $sellerUser->ledgersales->count() );
        $this->assertEquals( PaymentTypeEnum::TIP, $sellerUser->ledgersales[0]->fltype );
        $this->assertEquals( 'users', $sellerUser->ledgersales[0]->purchaseable_type );

        $this->assertEquals( 1, $buyerUser->ledgerpurchases->count() );
        $this->assertEquals( $sellerUser->ledgersales[0]->id, $buyerUser->ledgerpurchases[0]->id );

        $fl = $sellerUser->ledgersales()->where('purchaser_id', $buyerUser->id)->first();
        $this->assertEquals( $sellerUser->id, $fl->purchaseable_id );
        $this->assertEquals( 1, $fl->qty );
        $this->assertEquals( $payload['amount'], $fl->total_amount ); // no taxes applied
    }

    /**
     *  @group fldev
     */
    public function test_can_purchase_post()
    {
        // [ ] %TODO: RBAC permissions to access post
        // [ ] %TODO: test ledgersales & ledgerpurchases relations
        $buyerUser = $this->buyerUser;
        $sellerUser = $this->sellerUser;
        $post = Post::where('timeline_id', $sellerUser->timeline->id)->where('type', PostTypeEnum::PRICED)->first();
        $this->assertNotNull( $post->price );
        $this->assertGreaterThan( 0, $post->price );

        $payload = [ ];
        $response = $this->actingAs($buyerUser)->ajaxJSON('POST', route('posts.purchase', $post->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $postR = $content->post;
        $this->assertEquals($post->id, $postR->id);

        $post->refresh();
        $buyerUser->refresh();
        $sellerUser->refresh();

        $this->assertEquals( 1, $post->ledgersales->count() );
        $this->assertEquals( PaymentTypeEnum::PURCHASE, $post->ledgersales[0]->fltype );
        $this->assertEquals( 'posts', $post->ledgersales[0]->purchaseable_type );

        $this->assertEquals( 1, $buyerUser->ledgerpurchases->count() );
        $this->assertEquals( $post->ledgersales[0]->id, $buyerUser->ledgerpurchases[0]->id );

        $this->assertTrue( $buyerUser->sharedPosts->contains('id', $post->id) ); 

        $fl = $post->ledgersales()->where('purchaser_id', $buyerUser->id)->first();
        $this->assertEquals( $post->id, $fl->purchaseable_id );
        $this->assertEquals( 1, $fl->qty );
        $this->assertEquals( $payload['amount'], $fl->total_amount ); // no taxes applied
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->_deleteList = collect();

        $this->buyerUser = factory(\App\User::class)->create();
        $this->_deleteList->push($this->buyerUser);

        $this->sellerUser = factory(\App\User::class)->create();
        $this->_deleteList->push($this->sellerUser);

        // Create some posts for the 'seller user'
        $post = factory(Post::class)->create([
            'user_id'      => $this->sellerUser->id,
            'timeline_id'  => $this->sellerUser->timeline->id,
            'type'         => PostTypeEnum::FREE,
        ]);

        $post = factory(Post::class)->create([
            'user_id'      => $this->sellerUser->id,
            'timeline_id'  => $this->sellerUser->timeline->id,
            'type'         => PostTypeEnum::PRICED,
            'price'        => $this->faker->randomFloat(2, 1, 300),
        ]);

        $post = factory(Post::class)->create([
            'user_id'      => $this->sellerUser->id,
            'timeline_id'  => $this->sellerUser->timeline->id,
            'type'         => PostTypeEnum::SUBSCRIBER,
        ]);
    }

    protected function tearDown() : void {
        if ( !self::$_persist ) {
            while ( $this->_deleteList->count() > 0 ) {
                $obj = $this->_deleteList->pop();
                if ( $obj instanceof \App\User ) {
                     $obj->ledgersales->each( function($o) { $o->forceDelete(); } );
                     $obj->ledgerpurchases->each( function($o) { $o->forceDelete(); } );
                     $obj->posts->each( function($o) { $o->forceDelete(); } );
                     $obj->timeline->forceDelete();
                }
                $obj->forceDelete();
            }
        }
        parent::tearDown();
    }
}

