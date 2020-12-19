<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
//use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;
use Ramsey\Uuid\Uuid;
use App\Mediafile;
use App\Vault;
use App\Vaultfolder;
use App\Enums\MediafileTypeEnum;

class ShareableTest extends TestCase
{

    use WithFaker;

    private $_deleteList;
    private static $_persist = true;

    /*
    public function test_debug()
    {
        $mediafile = Mediafile::find(4);
        //$f = $s->mediafiles->first()->filename;
        $f = $mediafile->filename;
        //dd($f);
        //$s = Storage::disk('s3')->get($f);
        $s = Storage::disk('s3')->url($f);
        //$s = Storage::disk('s3')->get($s->mediafiles->first()->filename);
        dd($s);
        dd($mediafile->toArray());
    }
     */

    /**
     * @group sdev
     */
    public function test_can_share_mediafile()
    {
        $user = factory(\App\User::class)->create();
        $user->refresh();
        $this->_deleteList[] = $user;

        $vault = Vault::doCreate($this->faker->bs, $user);
        $rootVF = $vault->getRootFolder();
        $vault->refresh();
        $this->_deleteList->push($vault);

        $file = UploadedFile::fake()->image('file-foo.png', 400, 400);
        $mediafile = Mediafile::create([
            'resource_id'=>$rootVF->id,
            'resource_type'=>'vaultfolders',
            'filename'=>(string) Uuid::uuid4(),
            'mftype' => MediafileTypeEnum::VAULT,
            'mimetype' => $file->getMimeType(),
            'orig_filename' => $file->getClientOriginalName(),
            'orig_ext' => $file->getClientOriginalExtension(),
        ]);
        $mediafile->refresh();


        $user->mediafiles()->attach($mediafile->id);

        // --

        dump($mediafile);

        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->id);
        $this->assertNotNull($vault);
        $this->assertGreaterThan(0, $vault->id);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->_deleteList = collect();
    }

    protected function tearDown() : void {
        if ( !self::$_persist ) {
            while ( $this->_deleteList->count() > 0 ) {
                $obj = $this->_deleteList->pop();
                if ( $obj instanceof Vault ) {
                     $obj->vaultfolders()->delete();
                }
                $obj->delete();
            }
        }
        parent::tearDown();
    }

}
