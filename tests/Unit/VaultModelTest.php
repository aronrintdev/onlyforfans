<?php
namespace Tests\Unit;

use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;

//use Database\Seeders\TestDatabaseSeeder;
use Database\Seeders\TestMinimalDatabaseSeeder;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

use App\Models\User;
use App\Models\Vault;
use App\Models\Mediafile;
use App\Models\Vaultfolder;
use App\Enums\MediafileTypeEnum;

class VaultModelTest extends TestCase
{
    //use RefreshDatabase;
    use WithFaker;

    /**
     * @group vdev
     * @group regression
     * @group june28
     */
    public function test_can_get_vault_subfolder_tree()
    {
        $vault = Vault::has('vaultfolders')->firstOrFail();

        $rootFolder = $vault->getRootFolder();
        //dump('rootFolder1', $rootFolder);

        // --

        // Create some subfolders
        $attrs = [
            'vault_id' => $vault->id,
            'user_id' => $rootFolder->user_id,
        ];

        $attrs['parent_id'] = $rootFolder->id;
        $attrs['vfname'] = $this->faker->slug;
        $new = Vaultfolder::create($attrs);

        $attrs['parent_id'] = $new->id;
        $attrs['vfname'] = $this->faker->slug;
        $new = Vaultfolder::create($attrs);

        $rootFolder2 = Vaultfolder::with('vfchildren')->whereNull('parent_id')->where('vault_id', $vault->id)->firstorFail();

        // --

        $rootFolder->refresh();
        $subTree = $rootFolder->getSubTree();
        //dd('subTree', $subTree);

        //dd($rootFolder);

    }

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
        //$this->seed(TestMinimalDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }

}
