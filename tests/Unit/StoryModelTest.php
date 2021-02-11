<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;
use Ramsey\Uuid\Uuid;
use App\Models\MediaFile;
use App\Models\Story;
use App\Enums\MediaFileTypeEnum;

class StoryModelTest extends TestCase
{
    /**
     * @group mfdev
     */
    public function test_debug()
    {
        $mediaFile = MediaFile::find(4);
        //$f = $s->mediaFiles->first()->filename;
        $f = $mediaFile->filename;
        //dd($f);
        //$s = Storage::disk('s3')->get($f);
        $s = Storage::disk('s3')->url($f);
        //$s = Storage::disk('s3')->get($s->mediaFiles->first()->filename);
        dd($s);
        dd($mediaFile->toArray());
    }


    /**
     * @group OFF-mfdev
     */
    public function test_can_create_photo_story()
    {
        $story = factory(Story::class)->create();
        $story->mediaFiles()->save(factory(MediaFile::class)->create([
            'resource_type' => 'stories',
            'type' => MediaFileTypeEnum::STORY,
        ]));
        $story->refresh();
        $this->assertNotNull($story);
        $this->assertNotNull($story->id);
        $this->assertNotNull($story->mediaFiles);
        $this->assertNotNull($story->mediaFiles->first());
    }

}
