<?php
namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use DB;
use Ramsey\Uuid\Uuid;
use App\Mediafile;
use App\Vault;
use App\Vaultfolder;
use App\Enums\MediaFileTypeEnum;

class VaultModelTest extends TestCase
{

    private $_deleteList;
    private static $_persist = false;

    /**
     * @group mfdev
     */
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
     * @group vdev
     */
    public function test_can_create_vault()
    {
        $user = factory(\App\User::class)->create();
        $user->refresh();
        $this->_deleteList[] = $user;

        $vault = factory(Vault::class)->create([
            'user_id' => $user->id,
            'cattrs' => [
                'foo' => 'bar',
                'baz' => 'lorem',
            ],
        ]);
        $vault->refresh();
        $this->_deleteList->push($vault);

        $vfroot = factory(Vaultfolder::class)->create([
            'vault_id' => $vault->id,
        ]);
        $vfroot->refresh();
        $this->_deleteList->push($vfroot);

        $vfchild1 = factory(Vaultfolder::class)->create([
            'vault_id' => $vault->id,
            'parent_id' => $vfroot->id,
        ]);
        $vfchild1->refresh();
        $this->_deleteList->push($vfchild1);

        // --

        $this->assertNotNull($user);

        $this->assertNotNull($vault);
        $this->assertNotNull($vault->id);
        $this->assertGreaterThan(0, $vault->id);
        $this->assertEquals('bar', $vault->cattrs['foo']);

        $this->assertNotNull($vfroot);
        $this->assertNotNull($vfroot->id);
        $this->assertGreaterThan(0, $vfroot->id);
        $this->assertNull($vfroot->parent_id);

        $this->assertNotNull($vfchild1);
        $this->assertNotNull($vfchild1->id);
        $this->assertGreaterThan(0, $vfchild1->id);
        $this->assertEquals($vfroot->id, $vfchild1->parent_id);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->_deleteList = collect();
    }
    protected function tearDown() : void {
        if ( !self::$_persist ) {
            while ( $this->_deleteList->count() > 0 ) {
                $obj = $this->_deleteList->pop();
                $obj->delete();
            }
        }
        parent::tearDown();
    }

}
