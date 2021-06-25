<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase; 
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\Invite;
use App\Models\Mediafile;
use App\Models\Post;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Vault;
use App\Models\Vaultfolder;
use App\Enums\MediafileTypeEnum;
use App\Enums\PostTypeEnum;
use App\Enums\StoryTypeEnum;

class RestVaultTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_list_all_of_my_vault_folders()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $primaryVault = Vault::primary($creator)->first();

        $payload = [
            'vault_id' => $primaryVault->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $vaultfoldersR = collect($content->data);

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
    public function test_can_not_list_other_non_shared_vault_folders()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();

        $nonfan = User::whereDoesntHave('sharedvaultfolders', function($q1) use(&$primaryVault) {
            $q1->where('vault_id', $primaryVault->id); // %FIXME: should this be on vaultfolder?
        })->where('id', '<>', $creator->id)->first();

        $payload = [
            'vault_id' => $primaryVault->id,
        ];
        $response = $this->actingAs($nonfan)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_list_my_root_level_vault_folders()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $primaryVault = Vault::primary($creator)->first();

        $payload = [
            'vault_id' => $primaryVault->id,
            'parent_id' => 'root',
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultfolders.index'), $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $vaultfoldersR = collect($content->data);
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
    public function test_can_create_a_new_vault_folder()
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
    public function test_can_not_create_a_new_vault_folder_bad_param_422()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $origNumChildren = $rootFolder->vfchildren->count();

        $payload = [
            'vault_id' => $primaryVault->id,
            'parent_id' => $rootFolder->id,
            'vfname' => $this->faker->slug.'/',
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(422);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_not_create_a_new_vault_folder_with_same_name_422()
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

        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(422);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_not_create_a_new_vault_folder_403()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $nonOwnedVault = Vault::where('user_id', '<>', $creator->id)->first();

        $payload = [
            'vault_id' => $nonOwnedVault->id,
            'parent_id' => $rootFolder->id,
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_navigate_my_vault_folders()
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

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_update_my_vault_folder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // rename the root folder
        $payload = [
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('vaultfolders.update', $rootFolder->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $vaultfolderR = $content->vaultfolder;
        $this->assertEquals($rootFolder->id, $vaultfolderR->id);

        $this->assertNotSame($payload['vfname'], $rootFolder->name, 'Pre-updated root folder name should not match payload param');
        $rootFolder->refresh();
        $this->assertSame($payload['vfname'], $rootFolder->name, 'Updated root folder name should match payload param');
        $this->assertNull($rootFolder->parent_id, 'Updated root folder parent should still be null');

        // create a subfolder
        $origSubfolderName = $this->faker->slug;
        $payload = [
            'vault_id' => $rootFolder->vault_id, // $primaryVault->id,
            'parent_id' => $rootFolder->id,
            'vfname' => $origSubfolderName,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $subfolderR = $content->vaultfolder;
        $rootFolder->refresh();

        // rename the new subfolder
        $updatedSubfolderName = $this->faker->slug;
        $payload = [
            'vfname' => $updatedSubfolderName,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('vaultfolders.update', $subfolderR->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $subfolder = Vaultfolder::find($content->vaultfolder->id);

        $this->assertNotSame($origSubfolderName, $subfolder->name, 'Updated sub-folder name should not match original');
        $this->assertSame($updatedSubfolderName, $subfolder->name, 'Updated sub-folder name should match new value');
        $this->assertEquals($rootFolder->id, $subfolder->parent_id, 'Updated sub-folder parent should still be root folder');
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_delete_my_non_root_vault_folder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // make a subfolder
        $payload = [
            'vault_id' => $rootFolder->vault_id,
            'parent_id' => $rootFolder->id,
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $subfolderR = $content->vaultfolder;
        $rootFolder->refresh();
        $exists = Vaultfolder::find($subfolderR->id);
        $this->assertNotNull($exists);
        $this->assertTrue($rootFolder->vfchildren->contains($subfolderR->id), 'Root should now contain newly created subfolder');

        // delete the subfolder
        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('vaultfolders.destroy', $subfolderR->id));
        $response->assertStatus(200);
        $rootFolder->refresh();
        $exists = Vaultfolder::find($subfolderR->id);
        $this->assertNull($exists);
        $this->assertFalse($rootFolder->vfchildren->contains($subfolderR->id), 'Root should not contain deleted subfolder');
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_nonowner_can_not_delete_my_vault_folder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $nonowner = User::where('id', '<>', $creator->id)->first();

        // make a subfolder
        $payload = [
            'vault_id' => $rootFolder->vault_id,
            'parent_id' => $rootFolder->id,
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $subfolderR = $content->vaultfolder;
        $rootFolder->refresh();
        $exists = Vaultfolder::find($subfolderR->id);
        $this->assertNotNull($exists);
        $this->assertTrue($rootFolder->vfchildren->contains($subfolderR->id), 'Root should now contain newly created subfolder');

        // delete the subfolder
        $response = $this->actingAs($nonowner)->ajaxJSON('DELETE', route('vaultfolders.destroy', $subfolderR->id));
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_not_delete_my_root_vault_folder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // try to delete root folder
        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('vaultfolders.destroy', $rootFolder->id));
        $response->assertStatus(400);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_upload_single_image_file_to_my_vault_folder()
    {
        Storage::fake('s3');

        $owner = User::first();
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafileR = $content->mediafile;

        // $mediafile = Mediafile::where('resource_type', 'vaultfolders')->where('resource_id',
        // $vaultfolder->id)->first(); // %FIXME: this assumes there are no prior images in the vault
        $mediafile = Mediafile::find($mediafileR->id);
        $this->assertNotNull($mediafile);
        $this->assertEquals('vaultfolders', $mediafile->resource_type);
        $this->assertEquals($vaultfolder->id, $mediafile->resource_id);
        Storage::disk('s3')->assertExists($mediafile->filename);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame(MediafileTypeEnum::VAULT, $mediafile->mftype);

        // Test relations
        $this->assertTrue( $vaultfolder->mediafiles->contains($mediafile->id) );
        $this->assertEquals( $vaultfolder->id, $mediafile->resource->id );
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_upload_multiple_image_files_to_my_vault_folder()
    {
        Storage::fake('s3');

        $owner = User::first();
        $filename1 = $this->faker->slug;
        $file1 = UploadedFile::fake()->image($filename1, 200, 200);
        $filename2 = $this->faker->slug;
        $file2 = UploadedFile::fake()->image($filename2, 300, 270);

        $primaryVault = Vault::primary($owner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file1,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file2,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        $mediafiles = Mediafile::where('resource_type', 'vaultfolders')->get();
        $this->assertNotNull($mediafiles);
        $this->assertEquals(2, $mediafiles->count());

        $mf1 = $mediafiles->shift();
        Storage::disk('s3')->assertExists($mf1->filename);
        $this->assertSame($filename1, $mf1->mfname);
        $this->assertSame(MediafileTypeEnum::VAULT, $mf1->mftype);

        // Test relations
        $this->assertTrue( $vaultfolder->mediafiles->contains($mf1->id) );
        $this->assertEquals( $vaultfolder->id, $mf1->resource->id );

        $mf2 = $mediafiles->shift();
        Storage::disk('s3')->assertExists($mf2->filename);
        $this->assertSame($filename2, $mf2->mfname);
        $this->assertSame(MediafileTypeEnum::VAULT, $mf2->mftype);

        // Test relations
        $this->assertTrue( $vaultfolder->mediafiles->contains($mf2->id) );
        $this->assertEquals( $vaultfolder->id, $mf2->resource->id );
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_nonowner_can_not_upload_image_to_my_vault_folder()
    {
        Storage::fake('s3');

        $filename = $this->faker->slug;
        $owner = User::first();
        $nonowner = User::where('id', '<>', $owner->id)->first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($nonowner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    // Creates post in first API call, then attaches selected mediafile in a second API call (associates as a reference)
    public function test_can_select_mediafile_from_vault_folder_to_attach_to_post_by_attach()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $fan = $timeline->followers[0];

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = Mediafile::find($content->mediafile->id);
        $this->assertNotNull($mediafile);
        $this->assertTrue( $vaultfolder->mediafiles->contains($mediafile->id) );

        // --- Create a free post with image from vault ---

        //$filename = $this->faker->slug;
        //$file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $post = Post::findOrFail($postR->id);

        $payload = [
            //'mftype' => MediafileTypeEnum::VAULT,
            'mftype' => MediafileTypeEnum::POST,
            'mediafile_id' => $mediafile->id, // the presence of this field is what tells controller method to create a reference, not upload content
            'resource_type' => 'posts',
            'resource_id' => $post->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // --

        $timeline->refresh();
        $owner->refresh();
        $post->refresh();
        $mediafile = $post->mediafiles->shift();
        $this->assertNotNull($mediafile, 'No mediafiles attached to post');

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group vault
     *  @group regression
     */
    // Given one user who owns a vault + mediafile, and a different user who owns a timeline/post, 
    // neither user should be able to attach the mediafile to the post
    public function test_nonowner_can_not_select_vault_folder_mediafile_to_attach_to_post_by_attach()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $postOwner = $timeline->user;
        $mediafileowner = User::where('id', '<>', $postOwner->id)->first();

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($mediafileowner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root
        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($mediafileowner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $mediafile = Mediafile::findOrFail($content->mediafile->id);

        // --- Create a free post ---
        //$filename = $this->faker->slug;
        //$file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($postOwner)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $post = Post::findOrFail($content->post->id);

        // --- Try to attach image to post as post owner (but not mediafile owner) ---
        //$response = $this->actingAs($postOwner)->ajaxJSON('PATCH', route('posts.attachMediafile', [$post->id, $mediafile->id]));
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile_id' => $mediafile->id,
            'resource_type' => 'posts',
            'resource_id' => $post->id,
        ];
        $response = $this->actingAs($postOwner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(403);

        // --- Try to attach image to post as mediafile owner (but not post owner) ---
        //$response = $this->actingAs($mediafileowner)->ajaxJSON('PATCH', route('posts.attachMediafile', [$post->id, $mediafile->id]));
        $payload = [
            'mftype' => MediafileTypeEnum::POST,
            'mediafile_id' => $mediafile->id,
            'resource_type' => 'posts',
            'resource_id' => $post->id,
        ];
        $response = $this->actingAs($postOwner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    // Creates story in first API call, then attaches selected mediafile in a second API call (associates as a reference)
    public function test_can_select_mediafile_from_vault_folder_to_attach_to_story_by_attach()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $fan = $timeline->followers[0];

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = Mediafile::find($content->mediafile->id);
        $this->assertNotNull($mediafile);
        $this->assertTrue( $vaultfolder->mediafiles->contains($mediafile->id) );

        // --- Create a new story with image from vault ---

        $stype = StoryTypeEnum::PHOTO;
        $bgcolor = 'blue';
        $content = $this->faker->realText;

        $payload = [
            'stype' => $stype,
            'bgcolor' => $bgcolor,
            'content' => $content,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;
        $story = Story::findOrFail($storyR->id);

        // --- Create a free post with image from vault ---

        //$filename = $this->faker->slug;
        //$file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $post = Post::findOrFail($postR->id);

        $payload = [
            'mftype' => MediafileTypeEnum::STORY,
            'mediafile_id' => $mediafile->id, // the presence of this field is what tells controller method to create a reference, not upload content
            'resource_type' => 'stories',
            'resource_id' => $story->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);

        // --

        $timeline->refresh();
        $owner->refresh();
        $story->refresh();
        $mediafile = $story->mediafiles->shift();
        $this->assertNotNull($mediafile, 'No mediafiles attached to story');

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.show', $story->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(200);
    }

    // -----

    /**
     *  @group vault
     *  @group regression
     */
    // Creates post and attaches selected mediafile in a single API call
    public function test_can_select_mediafile_from_vault_folder_to_attach_to_post_single_op()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $fan = $timeline->followers[0];

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = Mediafile::find($content->mediafile->id);
        $this->assertNotNull($mediafile);
        $this->assertTrue( $vaultfolder->mediafiles->contains($mediafile->id) );

        // --- Create a free post with image from vault ---

        //$filename = $this->faker->slug;
        //$file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'mediafiles' => [$mediafile->id], // %NOTE: sends array
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('posts.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->post);
        $postR = $content->post;
        $post = Post::findOrFail($postR->id);

        // --

        $timeline->refresh();
        $owner->refresh();
        $post->refresh();
        $mediafile = $post->mediafiles->shift();
        $this->assertNotNull($mediafile, 'No mediafiles attached to post');

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(200);
    }
    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_select_mediafile_from_vault_folder_to_attach_to_story_single_op()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $fan = $timeline->followers[0];

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = Mediafile::find($content->mediafile->id);
        $this->assertNotNull($mediafile);
        $this->assertTrue( $vaultfolder->mediafiles->contains($mediafile->id) );

        // --- Create a free story with image from vault ---

        $payload = [
            'stype' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
            'mediafile' => $mediafile->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;
        $story = Story::findOrFail($storyR->id);

        // --

        $timeline->refresh();
        $owner->refresh();
        $story->refresh();
        $mediafile = $story->mediafiles->shift();
        $this->assertNotNull($mediafile, 'No mediafiles attached to story');

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.show', $story->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediafiles.show', $mediafile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group vault
     *  @group OFF-regression: this test only makes sense if we pass the timeline_id which the story will be added to
     */
    /*
    public function test_nonowner_can_not_select_mediafile_from_vault_folder_to_attach_to_story_single_op()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $storyOwner = $timeline->user;
        $mediafileowner = User::where('id', '<>', $storyOwner->id)->first();

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($mediafileowner)->first();
        $vaultfolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $vaultfolder->id,
        ];
        $response = $this->actingAs($mediafileowner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = Mediafile::find($content->mediafile->id);
        $this->assertNotNull($mediafile);
        $this->assertTrue( $vaultfolder->mediafiles->contains($mediafile->id) );

        // --- Create a free story with image from vault ---

        $payload = [
            'attrs' => json_encode([ 'stype' => StoryTypeEnum::PHOTO, 'content' => $this->faker->realText ]),
            'mediafile' => $mediafile->id,
        ];
        $response = $this->actingAs($mediafileowner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(403);
        $response = $this->actingAs($storyOwner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(403);
    }
     */

    /**
     *  @group vault
     *  @group regression
     */
    public function test_select_vault_folder_to_share_via_signup_invite_to_non_registered_user_via_email()
    {
        Mail::fake();

        $owner = User::first();
        $primaryVault = Vault::primary($owner)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // make a subfolder
        $payload = [
            'vault_id' => $primaryVault->id,
            'parent_id' => $rootFolder->id,
            'vfname' => $this->faker->slug,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('vaultfolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultfolder);
        $subFolder = Vaultfolder::find($content->vaultfolder->id);
        $this->assertNotNull($subFolder);
        $rootFolder->refresh();
        //$rootFolder->load('vfchildren');

        $invitees = [];
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName;
        $email = strtolower($firstName.'.'.$lastName).'@example.com';
        $invitees[] = [
            'email' => $email,
            'vfname' => $firstName.' '.$lastName,
        ];
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName;
        $email = strtolower($firstName.'.'.$lastName).'@example.com';
        $invitees[] = [
            'email' => $email,
            'vfname' => $firstName.' '.$lastName,
        ];

        // share the subfolder
        $payload = [
            'invitees' => $invitees,
            'shareables' => [
                [ 'shareable_type' => 'vaultfolders', 'shareable_id' => $subFolder->id, ],
            ],
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('invites.shareVaultResources', $subFolder->id), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->invites);
        $invites = collect($content->invites);
        $this->assertEquals(2, $invites->count());

        $invite = $invites->shift();
        $this->assertEquals($invitees[0]['email'], $invite->email);

        // assertSent | assertQueued
        Mail::assertQueued(\App\Mail\ShareableInvited::class, function ($mail) use ($invitees) {
            return $mail->hasTo($invitees[0]['email']); 
        });
        Mail::assertQueued(\App\Mail\ShareableInvited::class, function ($mail) use ($invitees) {
            return $mail->hasTo($invitees[1]['email']); 
        });

    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_select_subset_of_mediafiles_in_vaultfolder_to_share_via_signup_invite_to_non_registered_user_via_email()
    {
        Mail::fake();
        Storage::fake('s3');

        $owner = User::first();
        $primaryVault = Vault::primary($owner)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // Upload a few images to the vaultfolder...
        // (1st)
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $rootFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafileR = $content->mediafile;
        $rootFolder->refresh();
        $this->assertTrue( $rootFolder->mediafiles->contains($mediafileR->id) );

        // (2nd) %TODO

        $invitees = [];
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName;
        $email = strtolower($firstName.'.'.$lastName).'@example.com';
        $invitees[] = [
            'email' => $email,
            'vfname' => $firstName.' '.$lastName,
        ];

        // find mediafile(s) in the folder and share file(s) only
        $payload = [
            'invitees' => $invitees,
            'shareables' => [
                [ 'shareable_type' => 'mediafiles', 'shareable_id' => $mediafileR->id, ],
            ],
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('invites.shareVaultResources', $rootFolder->id), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->invites);
        $invites = collect($content->invites);
        $this->assertEquals(1, $invites->count());

        $invite = $invites->shift();
        $this->assertEquals($invitees[0]['email'], $invite->email);

        // assertSent | assertQueued
        Mail::assertQueued(\App\Mail\ShareableInvited::class, function ($mail) use ($invitees) {
            return $mail->hasTo($invitees[0]['email']); 
        });
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_select_vaultfolder_to_share_with_registered_user()
    {
        Mail::fake();
        Storage::fake('s3');

        $owner = User::first();
        $primaryVault = Vault::primary($owner)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $nonowner = User::where('id', '<>', $owner->id)
            ->whereDoesntHave('sharedvaultfolders', function($q1) use(&$rootFolder) {
                $q1->where('vaultfolders.id', $rootFolder->id);
            })->first(); // %TODO: also ensure not a sharee already? or create new user
        $this->assertFalse( $rootFolder->sharees->contains($nonowner->id) );

        // Upload a few images to the vaultfolder...
        // (1st)
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $rootFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafileR = $content->mediafile;
        $rootFolder->refresh();
        $this->assertTrue( $rootFolder->mediafiles->contains($mediafileR->id) );

        // (2nd) %TODO

        $sharees = [];
        $sharees[] = [
            'id' => $nonowner->id,
        ];

        // share the folder
        $payload = [
            'sharees' => $sharees,
            'shareables' => [
                [ 'shareable_type' => 'vaultfolders', 'shareable_id' => $rootFolder->id, ],
            ],
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('vaultfolders.share', $rootFolder->id), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->sharees);
        $sharees = collect($content->sharees);
        $this->assertEquals(1, $sharees->count());

        $response = $this->actingAs($nonowner)->ajaxJSON('GET', route('mediafiles.show', $mediafileR->id));
        $response->assertStatus(200);

    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_select_mediafiles_in_vaultfolder_to_share_with_registered_user()
    {
        Mail::fake();
        Storage::fake('s3');

        $owner = User::first();
        $primaryVault = Vault::primary($owner)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $nonowner = User::where('id', '<>', $owner->id)
            ->whereDoesntHave('sharedvaultfolders', function($q1) use(&$rootFolder) {
                $q1->where('vaultfolders.id', $rootFolder->id);
            })->first(); // %TODO: also ensure not a sharee already? or create new user
        $this->assertFalse( $rootFolder->sharees->contains($nonowner->id) );

        // Upload a few images to the vaultfolder...
        // (1st)
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $file,
            'resource_type' => 'vaultfolders',
            'resource_id' => $rootFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafileR = $content->mediafile;
        $rootFolder->refresh();
        $this->assertTrue( $rootFolder->mediafiles->contains($mediafileR->id) );

        // (2nd) %TODO

        $sharees = [];
        $sharees[] = [
            'id' => $nonowner->id,
        ];

        // share the folder
        $payload = [
            'sharees' => $sharees,
            'shareables' => [
                [ 'shareable_type' => 'mediafiles', 'shareable_id' => $mediafileR->id, ],
            ],
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('vaultfolders.share', $rootFolder->id), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->sharees);
        $sharees = collect($content->sharees);
        $this->assertEquals(1, $sharees->count());

        $response = $this->actingAs($nonowner)->ajaxJSON('GET', route('mediafiles.show', $mediafileR->id));
        $response->assertStatus(200);

        $nonowner2 = User::where('id', '<>', $owner->id)
            ->whereDoesntHave('sharedvaultfolders', function($q1) use(&$rootFolder) {
                $q1->where('vaultfolders.id', $rootFolder->id);
            })->first(); // %TODO: also ensure not a sharee already? or create new user
        $response = $this->actingAs($nonowner2)->ajaxJSON('GET', route('mediafiles.show', $mediafileR->id));
        $response->assertStatus(403);
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

    /**
     *  @group vault
     *  @group regression
     *  @group todo (?)
     */
    /*
    public function test_can_not_share_root_vaultfolder()
    {
        Mail::fake();

        $owner = User::first();
        $primaryVault = Vault::primary($owner)->first();
        $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $invitees = [];
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName;
        $email = strtolower($firstName.'.'.$lastName).'@example.com';
        $invitees[] = [
            'email' => $email,
            'vfname' => $firstName.' '.$lastName,
        ];

        // try to share the rootfolder
        $payload = [
            'invitees' => $invitees,
            'vaultfolder_id' => $rootFolder->id,
            'shareables' => [
                'shareable_type' => 'vaultfolders',
                'shareable_id' => $rootFolder->id,
            ],
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('invites.store'), $payload);
        $response->assertStatus(400);
    }
     */

