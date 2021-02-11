<?php
namespace Tests\Unit;

use DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vault;
use Ramsey\Uuid\Uuid;
use App\Models\MediaFile;
use App\Models\VaultFolder;
use App\Enums\MediaFileTypeEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShareableTest extends TestCase
{

    use WithFaker;

    private $_deleteList;
    private static $_persist = false;

    /**
     * @group OFF-sdev
     */
    public function test_can_share_media_file()
    {
        $user = factory(User::class)->create();
        $user->refresh();
        $this->_deleteList[] = $user;

        $vault = Vault::doCreate($this->faker->bs, $user);
        $rootVF = $vault->getRootFolder();
        $vault->refresh();
        $this->_deleteList->push($vault);

        $file = UploadedFile::fake()->image('file-foo.png', 400, 400);
        $mediaFile = MediaFile::create([
            'resource_id'=>$rootVF->id,
            'resource_type'=>'vaultFolders',
            'filename'=>(string) Uuid::uuid4(),
            'type' => MediaFileTypeEnum::VAULT,
            'mimetype' => $file->getMimeType(),
            'orig_filename' => $file->getClientOriginalName(),
            'orig_ext' => $file->getClientOriginalExtension(),
        ]);
        $mediaFile->refresh();
        $this->_deleteList->push($mediaFile);

        $user->sharedMediaFiles()->attach($mediaFile->id); // do share

        // --

        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->id);
        $this->assertNotNull($vault);
        $this->assertGreaterThan(0, $vault->id);
        $this->assertNotNull($mediaFile);
        $this->assertGreaterThan(0, $mediaFile->id);

        $this->assertGreaterThan(0, $user->sharedMediaFiles->count());
        $this->assertNotNull($user->sharedMediaFiles[0]);
        $this->assertSame($mediaFile->guid, $user->sharedMediaFiles[0]->guid);

        $this->assertGreaterThan(0, $mediaFile->sharees()->count());
        $this->assertNotNull($mediaFile->sharees[0]);
        $this->assertInstanceOf(User::class, $mediaFile->sharees[0]);
        $this->assertSame($user->id, $mediaFile->sharees[0]->id);
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

        $vaultFolder = $rootVF;
        $vaultFolder->refresh();

        $user->sharedVaultFolders()->attach($vaultFolder->id); // do share

        // --

        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->id);
        $this->assertNotNull($vault);
        $this->assertGreaterThan(0, $vault->id);
        $this->assertNotNull($vaultFolder);
        $this->assertGreaterThan(0, $vaultFolder->id);

        $this->assertGreaterThan(0, $user->sharedVaultFolders->count());
        $this->assertNotNull($user->sharedVaultFolders[0]);
        $this->assertSame($vaultFolder->guid, $user->sharedVaultFolders[0]->guid);

        $this->assertGreaterThan(0, $vaultFolder->sharees()->count());
        $this->assertNotNull($vaultFolder->sharees[0]);
        $this->assertInstanceOf(User::class, $vaultFolder->sharees[0]);
        $this->assertSame($user->id, $vaultFolder->sharees[0]->id);
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
                     $obj->vaultFolders()->delete();
                }
                if ( $obj instanceof MediaFile ) {
                     $obj->sharees()->detach();
                }
                /*
                if ( $obj instanceof User ) {
                     $obj->sharedMediaFiles()->detach();
                }
                 */
                $obj->delete();
            }
        }
        parent::tearDown();
    }

}
