<?php
namespace Tests\Unit;

use DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vault;
use Ramsey\Uuid\Uuid;
use App\Models\Mediafile;
use App\Models\Vaultfolder;
use App\Enums\MediafileTypeEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;

class ShareableTest extends TestCase
{

    use WithFaker;

    private $_deleteList;
    private static $_persist = false;

    /**
     * @group OFF-sdev
     */
    public function test_can_share_mediafile()
    {
        $user = factory(User::class)->create();
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
        $this->_deleteList->push($mediafile);

        $user->sharedmediafiles()->attach($mediafile->id); // do share

        // --

        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->id);
        $this->assertNotNull($vault);
        $this->assertGreaterThan(0, $vault->id);
        $this->assertNotNull($mediafile);
        $this->assertGreaterThan(0, $mediafile->id);

        $this->assertGreaterThan(0, $user->sharedmediafiles->count());
        $this->assertNotNull($user->sharedmediafiles[0]);
        $this->assertSame($mediafile->guid, $user->sharedmediafiles[0]->guid);

        $this->assertGreaterThan(0, $mediafile->sharees()->count());
        $this->assertNotNull($mediafile->sharees[0]);
        $this->assertInstanceOf(User::class, $mediafile->sharees[0]);
        $this->assertSame($user->id, $mediafile->sharees[0]->id);
    }

    /**
     * @group sdev
     */
    public function test_can_share_vault_folder()
    {
        $user = factory(User::class)->create();
        $user->refresh();
        $this->_deleteList[] = $user;

        $vault = Vault::doCreate($this->faker->bs, $user);
        $rootVF = $vault->getRootFolder();
        $vault->refresh();
        $this->_deleteList->push($vault);

        $vaultfolder = $rootVF;
        $vaultfolder->refresh();

        $user->sharedvaultfolders()->attach($vaultfolder->id); // do share

        // --

        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->id);
        $this->assertNotNull($vault);
        $this->assertGreaterThan(0, $vault->id);
        $this->assertNotNull($vaultfolder);
        $this->assertGreaterThan(0, $vaultfolder->id);

        $this->assertGreaterThan(0, $user->sharedvaultfolders->count());
        $this->assertNotNull($user->sharedvaultfolders[0]);
        $this->assertSame($vaultfolder->guid, $user->sharedvaultfolders[0]->guid);

        $this->assertGreaterThan(0, $vaultfolder->sharees()->count());
        $this->assertNotNull($vaultfolder->sharees[0]);
        $this->assertInstanceOf(User::class, $vaultfolder->sharees[0]);
        $this->assertSame($user->id, $vaultfolder->sharees[0]->id);
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
                if ( $obj instanceof Mediafile ) {
                     $obj->sharees()->detach();
                }
                /*
                if ( $obj instanceof User ) {
                     $obj->sharedmediafiles()->detach();
                }
                 */
                $obj->delete();
            }
        }
        parent::tearDown();
    }

}
