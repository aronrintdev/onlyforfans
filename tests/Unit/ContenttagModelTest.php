<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;

use Carbon\Carbon;
use Tests\TestCase;

use App\Models\Mediafile;
use App\Models\Contenttag;
use App\Models\User;

class ContenttagModelTest extends TestCase
{

    /**
     * @group contenttag-model
     * @group here0812
     */
    public function test_should_add_tag_to_mediafile()
    {
        $str = 'foo-123456789'; // unlikely this will be seeded

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


}
