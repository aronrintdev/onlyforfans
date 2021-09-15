<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;

use Carbon\Carbon;
use Tests\TestCase;

use App\Models\Mediafile;
use App\Models\Post;
use App\Models\Contenttag;
use App\Models\Contenttaggable;
use App\Models\User;
use App\Enums\ContenttagAccessLevelEnum;

class ContenttagModelTest extends TestCase
{
    use WithFaker;

    /**
     * @group contenttag-model
     */
    public function test_should_add_tag_to_mediafile()
    {
        $str = $this->faker->slug.'-'.$this->faker->numberBetween(10000,99999);

        $mf = Mediafile::first();

        $origCount = $mf->contenttags->count();

        $mf->addTag($str);

        $mf->refresh();

        $obj = Contenttag::where('ctag', $str)->first();
        $this->assertNotEmpty($obj);
        $this->assertNotEmpty($obj->id);
        $this->assertEquals($str, $obj->ctag);

        $this->assertEquals($origCount+1, $mf->contenttags->count());
        $this->assertTrue( $mf->contenttags->contains($obj->id) );
    }


    /**
     * @group contenttag-model
     */
    public function test_should_add_batch_of_tags_to_post()
    {
        $max = $this->faker->numberBetween(1,10);
        $publicTags = collect();
        collect(range(0,$max))->each( function() use(&$publicTags) {
            $publicTags->push( $this->faker->slug.'-'.$this->faker->numberBetween(10000,99999) );
        });
        $max = $this->faker->numberBetween(1,10);
        $privateTags = collect();
        collect(range(0,$max))->each( function() use(&$privateTags) {
            $privateTags->push( $this->faker->slug.'-'.$this->faker->numberBetween(10000,99999) );
        });

        $post = Post::first();

        $origCount = $post->contenttags->count();

        $post->addTag($publicTags, ContenttagAccessLevelEnum::OPEN); // batch add as collection
        $post->addTag($privateTags->toArray(), ContenttagAccessLevelEnum::MGMTGROUP); // batch add as array

        $post->refresh();

        $expectedCount = $origCount + $privateTags->count() + $publicTags->count();
        $this->assertEquals($expectedCount, $post->contenttags->count());

        $num = $publicTags->reduce( function($acc, $str) use(&$post) {
            $ct = Contenttag::where('ctag',$str)->first();
            return $post->contenttags->contains($ct->id) ? ($acc+1) : $acc;
        }, 0);
        $this->assertEquals($publicTags->count(), $num);

        $num = $privateTags->reduce( function($acc, $str) use(&$post) {
            $ct = Contenttag::where('ctag',$str)->first();
            return $post->contenttags->contains($ct->id) ? ($acc+1) : $acc;
        }, 0);
        $this->assertEquals($privateTags->count(), $num);
    }

    /**
     * @group contenttag-model
     */
    public function test_should_remove_tag_from_post()
    {
        $max = 5;
        $publicTags = collect();
        collect(range(0,$max))->each( function() use(&$publicTags) {
            $publicTags->push( $this->faker->slug.'-'.$this->faker->numberBetween(10000,99999) );
        });

        $post = Post::first();

        $origCount = $post->contenttags->count();

        $post->addTag($publicTags, ContenttagAccessLevelEnum::OPEN); // batch add as collection

        $expectedCount = $origCount + $publicTags->count();
        $removedTagStr = $publicTags->pop();
        $post->contenttags()->detach(); // %NOTE: must do manually to effect a remove of single tag
        $post->addTag($publicTags, ContenttagAccessLevelEnum::OPEN); // batch add as collection
        $expectedCount -= 1;

        $post->refresh();

        $this->assertEquals($expectedCount, $post->contenttags->count());
        $this->assertFalse( $post->contenttags->contains( function($ct) use($removedTagStr) {
            return $ct->ctag === $removedTagStr;
        }) );
    }

    // [ ] public tag

    // [ ] private tag


    /**
     * @group contenttag-model
     */
    public function test_should_get_tags_for_mediafile()
    {
        $mf = Mediafile::has('contenttags')->firstOrFail();
        $this->assertGreaterThan(0, $mf->contenttags->count());
    }

    /**
     * @group contenttag-model
     */
    public function test_should_get_tagged_resources_for_tag()
    {
        $ct = Contenttag::has('mediafiles')->firstOrFail();
        $this->assertGreaterThan(0, $ct->mediafiles->count());

        $ct = Contenttag::has('posts')->firstOrFail();
        $this->assertGreaterThan(0, $ct->posts->count());

        $ct = Contenttag::has('vaultfolders')->firstOrFail();
        $this->assertGreaterThan(0, $ct->vaultfolders->count());
    }

    /**
     * @group contenttag-model
     */
    public function test_should_get_open_tags()
    {
    }

    /**
     * @group contenttag-model
     */
    public function test_should_get_mgmtgroup_tags()
    {
    }


}
