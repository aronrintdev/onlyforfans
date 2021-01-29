<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

use Database\Seeders\TestDatabaseSeeder;
use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\User;
use App\Post;

class PostTest extends TestCase
{
    //use RefreshDatabase, WithFaker;
    //use WithFaker;
    use DatabaseTransactions, WithFaker;

    /**
     *  @group postdev
     */
    public function test_can_index_posts()
    {
        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $creator = User::has('posts', '>=', 2)->first(); // assume non-admin (%FIXME)
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('posts.index'));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $postsR = $content->posts;
        //dd($postsR, $postsR[0]);
        $this->assertGreaterThan(0, count($postsR));
        $this->assertNotNull($postsR[0]->description);
        $this->assertNotNull($postsR[0]->timeline_id);
        $this->assertEquals($postsR[0]->timeline_id, $creator->timeline->id);
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

