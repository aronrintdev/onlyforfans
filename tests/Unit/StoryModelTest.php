<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;
use Ramsey\Uuid\Uuid;
use App\Models\Mediafile;
use App\Models\Story;
use App\Enums\MediafileTypeEnum;

class StoryModelTest extends TestCase
{
    /**
     * @group story-model
     */
    public function test_debug()
    {
        $mediafile = Mediafile::find(4);
        //$f = $s->mediafiles->first()->filename;
        $f = $mediafile->filename;
        //$s = Storage::disk('s3')->get($f);
        $s = Storage::disk('s3')->url($f);
        //$s = Storage::disk('s3')->get($s->mediafiles->first()->filename);
    }


    /**
     * @group OFF-mfdev
     */
    public function test_can_create_photo_story()
    {
        $story = factory(Story::class)->create();
        $story->mediafiles()->save(factory(Mediafile::class)->create([
            'resource_type' => 'stories',
            'mftype' => MediafileTypeEnum::STORY,
        ]));
        $story->refresh();
        $this->assertNotNull($story);
        $this->assertNotNull($story->id);
        $this->assertNotNull($story->mediafiles);
        $this->assertNotNull($story->mediafiles->first());
    }

}
