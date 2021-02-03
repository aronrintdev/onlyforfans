<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\User;
use App\Story;
use App\Mediafile;
use App\Enums\StoryTypeEnum;

class StoriesTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group regression
     *  @group this
     */
    public function test_can_store_text_story()
    {
        $user = User::first();

        $attrs = [
                'stype' => StoryTypeEnum::TEXT,
                'bgcolor' => 'blue',
                'content' => $this->faker->realText,
            ];

        $payload = [
            'attrs' => json_encode($attrs),
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('stories.store'), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;

        $this->assertSame($attrs['content'], $storyR->content);
        $this->assertSame($attrs['stype'], $storyR->stype);

        $story = Story::find($storyR->id);
        $this->assertNotNull($story);
        $this->assertSame($story->content, $storyR->content);
        $this->assertSame($story->stype, $storyR->stype);

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

