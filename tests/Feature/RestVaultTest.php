<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Mediafile;
use App\Timeline;
use App\User;
use App\Vault;
use App\Vaultfolder;
use App\Enums\MediafileTypeEnum;

class RestVaultTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_index_all_of_my_vaultfolders()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $primaryVault = Vault::primary($creator)->first();

        $payload = [
            'filters' => [
                'vault_id' => $primaryVault->id,
            ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolders);
        $vaultfoldersR = collect($content->vaultfolders);
        dd($vaultfoldersR);

        $this->assertGreaterThan(0, $vaultfoldersR->count());

        $nonOwned = $vaultfoldersR->filter( function($vf) use(&$primaryVault) {
            return $primaryVault->id !== $vf->vault_id; // %FIXME: impl dependency
        });
        $this->assertEquals(0, $nonOwned->count(), 'Returned a vaultfolder that does not belong to creator');
        $expectedCount = Vaultfolder::where('vault_id', $primaryVault->id)->count(); // %FIXME scope
        $this->assertEquals($expectedCount, $vaultfoldersR->count(), 'Number of vaultfolders returned does not match expected value');
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_not_index_other_nonshared_vaultfolders()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();

        $nonfan = User::whereDoesntHave('sharedvaultfolders', function($q1) use(&$primaryVault) {
            $q1->where('vault_id', $primaryVault->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [
            'filters' => [
                'vault_id' => $primaryVault->id,
            ],
        ];
        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_index_my_root_level_vaultfolders()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $primaryVault = Vault::primary($creator)->first();

        $payload = [
            'filters' => [
                'vault_id' => $primaryVault->id,
                'parent_id' => 'root',
            ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolders);
        $vaultfoldersR = collect($content->vaultfolders);
        $this->assertGreaterThan(0, $vaultfoldersR->count());

        $nonRoot = $vaultfoldersR->filter( function($vf) {
            return $vf->parent_id !== null;
        });
        $this->assertEquals(0, $nonRoot->count(), 'Returned a vaultfolder not in root folder');

        $expectedCount = Vaultfolder::where('vault_id', $primaryVault->id)
            ->whereNull('parent_id')
            ->count(); // %FIXME scope
        $this->assertEquals($expectedCount, $vaultfoldersR->count(), 'Number of vaultfolders returned does not match expected value');
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_create_a_new_vaultfolder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $origNumChildren = $rootFolder->vfchildren->count();

        $payload = [
            'vault_id' => $primaryVault->id,
            'parent_id' => $rootFolder->id,
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(201);
        $rootFolder->refresh();
        $this->assertEquals( $origNumChildren+1, $rootFolder->vfchildren->count() ); // should be +1

        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $vaultfolderR = $content->vaultfolder;
        $vaultfolder = Vaultfolder::find($vaultfolderR->id);
        $this->assertEquals($rootFolder->id, $vaultfolder->parent_id);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_not_create_a_new_vaultfolder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $nonownedVault = Vault::where('user_id', '<>', $creator->id)->first();

        $payload = [
            'vault_id' => $nonownedVault->id,
            'parent_id' => $rootFolder->id,
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     *  @group this
     */
    public function test_can_navigate_my_vaultfolders()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $this->assertEquals(0, $rootFolder->vfchildren->count(), 'Root should not have any subfolders');
        $this->assertNull($rootFolder->vfparent, 'Root should have null parent');

        // set cwf via api call
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaults.getRootFolder', $primaryVault->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $cwf = $content->vaultfolder; // root
        $this->assertEquals($rootFolder->id, $cwf->id, 'Current working folder (root) pkid should match root');
        $this->assertNull($cwf->vfparent, 'Current working folder (root) should not have null parent');
        $this->assertNotNull($cwf->vfchildren, 'Current working folder (root) should not have children (subfolders) attribute');
        $this->assertEquals(0, count($cwf->vfchildren), 'Current working folder should not have any subfolders');

        // ---

        // make a subfolder
        $payload = [
            'vault_id' => $cwf->vault_id, // $primaryVault->id,
            'parent_id' => $cwf->id, // $rootFolder->id,
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $childVaultfolderR = $content->vaultfolder;
        $rootFolder->refresh();
        $rootFolder->load('vfchildren');

        // test cwf children, expect subfolder
        $this->assertEquals(1, $rootFolder->vfchildren->count(), 'Root should have 1 subfolder');
        $this->assertTrue($rootFolder->vfchildren->contains($childVaultfolderR->id), 'Root subfolders should include the one just created');

        // refresh 'cwf' via api call
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.show', $cwf->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $cwf = $content->vaultfolder;
        $this->assertEquals($rootFolder->id, $cwf->id, 'Current working folder should still be root');
        $this->assertEquals(1, count($cwf->vfchildren), 'Current working folder should have 1 subfolder');

        // cd to subfolder
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.show', $cwf->vfchildren[0]->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $cwf = $content->vaultfolder;

        // test cwf parent, expect root
        $this->assertEquals($rootFolder->id, $cwf->parent_id, 'Current working folder parent should be root');

    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

