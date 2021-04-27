<?php
namespace Tests\Unit;

use Tests\TestCase;
use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\TestDatabaseSeeder;
use App\Enums\PostTypeEnum;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\User;

class FavoriteableTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    /**
     * @group favoritable-model
     * @group OFF-regression
     */
    public function test_can_favorite_free_post()
    {
        $post = Post::where('type', PostTypeEnum::FREE)->first();
        $fan = User::factory()->create();

        $favorite = Favorite::create([
            'user_id' => $fan->id,
            'favoritable_type' => 'posts',
            'favoritable_id' => $post->id,
        ]);
        $post->refresh();

        $this->assertNotNull($favorite);
        $this->assertNotNull($favorite->id);
        $this->assertNotNull($post->favorites);
        $this->assertNotNull($post->favorites[0]);
        $this->assertEquals($post->id, $post->favorites[0]->favoritable_id);
        $this->assertEquals('posts', $post->favorites[0]->favoritable_type);

        $this->assertInstanceOf(Post::class, $post->favorites[0]->favoritable);
        $this->assertEquals($fan->id, $post->favorites[0]->user->id);
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
