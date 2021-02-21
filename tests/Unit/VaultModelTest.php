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
use Illuminate\Foundation\Testing\RefreshDatabase;

class VaultModelTest extends TestCase
{

    private $_deleteList;
    private static $_persist = false;

    /**
     * @group vdev
     */
    public function test_can_create_vault()
    {
        $user = factory(User::class)->create();
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

        $root = factory(Vaultfolder::class)->create([
            'vault_id' => $vault->id,
        ]);
        $root->refresh();
        $this->_deleteList->push($root);

        $child1 = factory(Vaultfolder::class)->create([
            'vault_id' => $vault->id,
            'parent_id' => $root->id,
        ]);
        $child1->refresh();
        $this->_deleteList->push($child1);

        // --

        $this->assertNotNull($user);

        $this->assertNotNull($vault);
        $this->assertNotNull($vault->id);
        $this->assertGreaterThan(0, $vault->id);
        $this->assertEquals('bar', $vault->customAttributes['foo']);

        $this->assertNotNull($root);
        $this->assertNotNull($root->id);
        $this->assertGreaterThan(0, $root->id);
        $this->assertNull($root->parent_id);

        $this->assertNotNull($child1);
        $this->assertNotNull($child1->id);
        $this->assertGreaterThan(0, $child1->id);
        $this->assertEquals($root->id, $child1->parent_id);
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
