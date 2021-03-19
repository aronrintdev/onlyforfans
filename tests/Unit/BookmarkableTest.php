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
use App\Models\Bookmark;
use App\Models\Post;
use App\Models\User;

class BookmarkableTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    /**
     * @group bookmarkable-model
     * @group OFF-regression
     */
    public function test_can_bookmark_free_post()
    {
        $post = Post::where('type', PostTypeEnum::FREE)->first();
        $fan = User::factory()->create();

        $bookmark = Bookmark::create([
            'user_id' => $fan->id,
            'bookmarkable_type' => 'posts',
            'bookmarkable_id' => $post->id,
        ]);
        $post->refresh();

        $this->assertNotNull($bookmark);
        $this->assertNotNull($bookmark->id);
        $this->assertNotNull($post->bookmarks);
        $this->assertNotNull($post->bookmarks[0]);
        $this->assertEquals($post->id, $post->bookmarks[0]->bookmarkable_id);
        $this->assertEquals('posts', $post->bookmarks[0]->bookmarkable_type);

        $this->assertInstanceOf(Post::class, $post->bookmarks[0]->bookmarkable);
        $this->assertEquals($fan->id, $post->bookmarks[0]->user->id);
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
