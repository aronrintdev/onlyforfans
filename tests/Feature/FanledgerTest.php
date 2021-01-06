<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

use App\Enums\PostTypeEnum;
use App\User;
use App\Post;
//use App\Mediafile;

// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
// https://stackoverflow.com/questions/34455410/error-executing-putobject-on-aws-upload-fails

class FanledgerTest extends TestCase
{
    use WithFaker;

    private $_deleteList;
    private static $_persist = false;

    //private $admin;
    private $sessionUser = null;
    private $otherUser = null;


    /**
     *  @group fldev
     */
    public function test_can_send_tip_on_post()
    {
        $sessionUser = $this->sessionUser;
        $otherUser = $this->otherUser;
        $post = Post::where('timeline_id', $otherUser->timeline->id)->where('type', PostTypeEnum::FREE)->first();
        $payload = [
            'amount' => $this->faker->randomFloat(2, 1, 99),
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON('POST', route('posts.tip', $post->id), $payload);

        $response->assertStatus(200);

        dd( $response );
        dd( $response->post );
        $postR = $response['post']->toArray();
        $this->assertEquals($post->id, $postR->id);

        $post->refresh();
        //$this->assertEquals($post->tips);
        //$this->assertGreaterThan( 0, $post->tips->count() );
        $this->assertGreaterThan( 0, $post->fanledgers->count() );

        $fl = $post->fanledgers()->where('purchaser_id', $sessionUser->id)->get();
        dump('fl', $fl);
    }

    protected function setUp() : void
    {
        parent::setUp();
        $this->_deleteList = collect();

        $this->sessionUser = factory(\App\User::class)->create();
        $this->_deleteList->push($this->sessionUser);

        $this->otherUser = factory(\App\User::class)->create();
        $this->_deleteList->push($this->otherUser);

        // Create some posts for the 'other user'
        $post = factory(Post::class)->create([
            'user_id'      => $this->otherUser->id,
            'timeline_id'  => $this->otherUser->timeline->id,
            'type'         => PostTypeEnum::FREE,
        ]);

        $post = factory(Post::class)->create([
            'user_id'      => $this->otherUser->id,
            'timeline_id'  => $this->otherUser->timeline->id,
            'type'         => PostTypeEnum::PRICED,
            'price'        => $this->faker->randomFloat(2, 1, 300),
        ]);

        $post = factory(Post::class)->create([
            'user_id'      => $this->otherUser->id,
            'timeline_id'  => $this->otherUser->timeline->id,
            'type'         => PostTypeEnum::SUBSCRIBER,
        ]);
    }

    protected function tearDown() : void {
        if ( !self::$_persist ) {
            while ( $this->_deleteList->count() > 0 ) {
                $obj = $this->_deleteList->pop();
                if ( $obj instanceof User ) {
                     $obj->timeline()->delete();
                     $obj->posts()->delete();
                }
                $obj->delete();
            }
        }
        parent::tearDown();
    }
}

