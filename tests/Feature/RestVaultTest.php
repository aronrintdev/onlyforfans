<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\MediaFile;
use App\Models\Post;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultFolder;
use App\Enums\MediaFileTypeEnum;
use App\Enums\PostTypeEnum;
use App\Enums\StoryTypeEnum;

class RestVaultTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_index_all_of_my_vault_folders()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $primaryVault = Vault::primary($creator)->first();

        $payload = [
            'filters' => [
                'vault_id' => $primaryVault->id,
            ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultFolders.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolders);
        $vaultFoldersR = collect($content->vaultFolders);

        $this->assertGreaterThan(0, $vaultFoldersR->count());

        $nonOwned = $vaultFoldersR->filter( function($vf) use(&$primaryVault) {
            return $primaryVault->id !== $vf->vault_id; // %FIXME: impl dependency
        });
        $this->assertEquals(0, $nonOwned->count(), 'Returned a vaultFolder that does not belong to creator');
        $expectedCount = VaultFolder::where('vault_id', $primaryVault->id)->count(); // %FIXME scope
        $this->assertEquals($expectedCount, $vaultFoldersR->count(), 'Number of vaultFolders returned does not match expected value');
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_not_index_other_non_shared_vault_folders()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();

        $nonFan = User::whereDoesntHave('sharedVaultFolders', function($q1) use(&$primaryVault) {
            $q1->where('vault_id', $primaryVault->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [
            'filters' => [
                'vault_id' => $primaryVault->id,
            ],
        ];
        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('vaultFolders.index'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_index_my_root_level_vault_folders()
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
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultFolders.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolders);
        $vaultFoldersR = collect($content->vaultFolders);
        $this->assertGreaterThan(0, $vaultFoldersR->count());

        $nonRoot = $vaultFoldersR->filter( function($vf) {
            return $vf->parent_id !== null;
        });
        $this->assertEquals(0, $nonRoot->count(), 'Returned a vaultFolder not in root folder');

        $expectedCount = VaultFolder::where('vault_id', $primaryVault->id)
            ->whereNull('parent_id')
            ->count(); // %FIXME scope
        $this->assertEquals($expectedCount, $vaultFoldersR->count(), 'Number of vaultFolders returned does not match expected value');
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_create_a_new_vault_folder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $origNumChildren = $rootFolder->children->count();

        $payload = [
            'vault_id' => $primaryVault->id,
            'parent_id' => $rootFolder->id,
            'name' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultFolders.store'), $payload);
        $response->assertStatus(201);
        $rootFolder->refresh();
        $this->assertEquals( $origNumChildren+1, $rootFolder->children->count() ); // should be +1

        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $vaultFolderR = $content->vaultFolder;
        $vaultFolder = VaultFolder::find($vaultFolderR->id);
        $this->assertEquals($rootFolder->id, $vaultFolder->parent_id);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_not_create_a_new_vault_folder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $nonOwnedVault = Vault::where('user_id', '<>', $creator->id)->first();

        $payload = [
            'vault_id' => $nonOwnedVault->id,
            'parent_id' => $rootFolder->id,
            'name' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultFolders.store'), $payload);
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
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $this->assertEquals(0, $rootFolder->children->count(), 'Root should not have any subfolders');
        $this->assertNull($rootFolder->parent, 'Root should have null parent');

        // set cwf via api call
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaults.getRootFolder', $primaryVault->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $cwf = $content->vaultFolder; // root
        $this->assertEquals($rootFolder->id, $cwf->id, 'Current working folder (root) pkid should match root');
        $this->assertNull($cwf->parent, 'Current working folder (root) should not have null parent');
        $this->assertNotNull($cwf->children, 'Current working folder (root) should not have children (subfolders) attribute');
        $this->assertEquals(0, count($cwf->children), 'Current working folder should not have any subfolders');

        // ---

        // make a subfolder
        $payload = [
            'vault_id' => $cwf->vault_id, // $primaryVault->id,
            'parent_id' => $cwf->id, // $rootFolder->id,
            'name' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultFolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $childVaultFolderR = $content->vaultFolder;
        $rootFolder->refresh();
        $rootFolder->load('children');

        // test cwf children, expect subfolder
        $this->assertEquals(1, $rootFolder->children->count(), 'Root should have 1 subfolder');
        $this->assertTrue($rootFolder->children->contains($childVaultFolderR->id), 'Root subfolders should include the one just created');

        // refresh 'cwf' via api call
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultFolders.show', $cwf->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $cwf = $content->vaultFolder;
        $this->assertEquals($rootFolder->id, $cwf->id, 'Current working folder should still be root');
        $this->assertEquals(1, count($cwf->children), 'Current working folder should have 1 subfolder');

        // cd to subfolder
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('vaultFolders.show', $cwf->children[0]->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $cwf = $content->vaultFolder;

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
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // rename the root folder
        $payload = [
            'name' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('vaultFolders.update', $rootFolder->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $vaultFolderR = $content->vaultFolder;
        $this->assertEquals($rootFolder->id, $vaultFolderR->id);

        $this->assertNotSame($payload['name'], $rootFolder->name, 'Pre-updated root folder name should not match payload param');
        $rootFolder->refresh();
        $this->assertSame($payload['name'], $rootFolder->name, 'Updated root folder name should match payload param');
        $this->assertNull($rootFolder->parent_id, 'Updated root folder parent should still be null');

        // create a subfolder
        $origSubfolderName = $this->faker->slug;
        $payload = [
            'vault_id' => $rootFolder->vault_id, // $primaryVault->id,
            'parent_id' => $rootFolder->id,
            'name' => $origSubfolderName,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultFolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $subfolderR = $content->vaultFolder;
        $rootFolder->refresh();

        // rename the new subfolder
        $updatedSubfolderName = $this->faker->slug;
        $payload = [
            'name' => $updatedSubfolderName,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('PATCH', route('vaultFolders.update', $subfolderR->id), $payload);
        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $subfolder = VaultFolder::find($content->vaultFolder->id);

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
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // make a subfolder
        $payload = [
            'vault_id' => $rootFolder->vault_id,
            'parent_id' => $rootFolder->id,
            'name' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultFolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $subfolderR = $content->vaultFolder;
        $rootFolder->refresh();
        $exists = VaultFolder::find($subfolderR->id);
        $this->assertNotNull($exists);
        $this->assertTrue($rootFolder->children->contains($subfolderR->id), 'Root should now contain newly created subfolder');

        // delete the subfolder
        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('vaultFolders.destroy', $subfolderR->id));
        $response->assertStatus(200);
        $rootFolder->refresh();
        $exists = VaultFolder::find($subfolderR->id);
        $this->assertNull($exists);
        $this->assertFalse($rootFolder->children->contains($subfolderR->id), 'Root should not contain deleted subfolder');
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_nonowner_can_not_delete_my_vault_folder()
    {
        $creator = User::first();
        $primaryVault = Vault::primary($creator)->first();
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();
        $nonowner = User::where('id', '<>', $creator->id)->first();

        // make a subfolder
        $payload = [
            'vault_id' => $rootFolder->vault_id,
            'parent_id' => $rootFolder->id,
            'name' => $this->faker->slug,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('POST', route('vaultFolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $subfolderR = $content->vaultFolder;
        $rootFolder->refresh();
        $exists = VaultFolder::find($subfolderR->id);
        $this->assertNotNull($exists);
        $this->assertTrue($rootFolder->children->contains($subfolderR->id), 'Root should now contain newly created subfolder');

        // delete the subfolder
        $response = $this->actingAs($nonowner)->ajaxJSON('DELETE', route('vaultFolders.destroy', $subfolderR->id));
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
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // try to delete root folder
        $response = $this->actingAs($creator)->ajaxJSON('DELETE', route('vaultFolders.destroy', $rootFolder->id));
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
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediaFile);
        $mediaFileR = $content->mediaFile;

        // $mediaFile = MediaFile::where('resource_type', 'vaultFolders')->where('resource_id',
        // $vaultFolder->id)->first(); // %FIXME: this assumes there are no prior images in the vault
        $mediaFile = MediaFile::find($mediaFileR->id);
        $this->assertNotNull($mediaFile);
        $this->assertEquals('vaultFolders', $mediaFile->resource_type);
        $this->assertEquals($vaultFolder->id, $mediaFile->resource_id);
        Storage::disk('s3')->assertExists($mediaFile->filename);
        $this->assertSame($filename, $mediaFile->name);
        $this->assertSame(MediaFileTypeEnum::VAULT, $mediaFile->type);

        // Test relations
        $this->assertTrue( $vaultFolder->mediaFiles->contains($mediaFile->id) );
        $this->assertEquals( $vaultFolder->id, $mediaFile->resource->id );
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
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file1,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file2,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);

        $mediaFiles = MediaFile::where('resource_type', 'vaultFolders')->get();
        $this->assertNotNull($mediaFiles);
        $this->assertEquals(2, $mediaFiles->count());

        $mf1 = $mediaFiles->shift();
        Storage::disk('s3')->assertExists($mf1->filename);
        $this->assertSame($filename1, $mf1->name);
        $this->assertSame(MediaFileTypeEnum::VAULT, $mf1->type);

        // Test relations
        $this->assertTrue( $vaultFolder->mediaFiles->contains($mf1->id) );
        $this->assertEquals( $vaultFolder->id, $mf1->resource->id );

        $mf2 = $mediaFiles->shift();
        Storage::disk('s3')->assertExists($mf2->filename);
        $this->assertSame($filename2, $mf2->name);
        $this->assertSame(MediaFileTypeEnum::VAULT, $mf2->type);

        // Test relations
        $this->assertTrue( $vaultFolder->mediaFiles->contains($mf2->id) );
        $this->assertEquals( $vaultFolder->id, $mf2->resource->id );
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
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($nonowner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    // Creates post in first API call, then attaches selected mediaFile in a second API call
    public function test_can_select_mediaFile_from_vault_folder_to_attach_to_post_by_attach()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $fan = $timeline->followers[0];

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediaFile);
        $mediaFile = MediaFile::find($content->mediaFile->id);
        $this->assertNotNull($mediaFile);
        $this->assertTrue( $vaultFolder->mediaFiles->contains($mediaFile->id) );

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

        $response = $this->actingAs($owner)->ajaxJSON('PATCH', route('posts.attachMediaFile', [$post->id, $mediaFile->id]));
        $response->assertStatus(200);

        // --

        $timeline->refresh();
        $owner->refresh();
        $post->refresh();
        $mediaFile = $post->mediaFiles->shift();
        $this->assertNotNull($mediaFile, 'No mediaFiles attached to post');

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediaFiles.show', $mediaFile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_nonowner_can_not_select_vault_folder_mediaFile_to_attach_to_post_by_attach()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $postOwner = $timeline->user;
        $mediaFileowner = User::where('id', '<>', $postOwner->id)->first();

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($mediaFileowner)->first();
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root
        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($mediaFileowner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $mediaFile = MediaFile::findOrFail($content->mediaFile->id);

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

        // --- Try to attach image to post as post owner (but not mediaFile owner) ---
        $response = $this->actingAs($postOwner)->ajaxJSON('PATCH', route('posts.attachMediaFile', [$post->id, $mediaFile->id]));
        $response->assertStatus(403);

        // --- Try to attach image to post as mediaFile owner (but not post owner) ---
        $response = $this->actingAs($mediaFileowner)->ajaxJSON('PATCH', route('posts.attachMediaFile', [$post->id, $mediaFile->id]));
        $response->assertStatus(403);
    }

    /**
     *  @group vault
     *  @group regression
     */
    // Creates post and attaches selected mediaFile in a single API call
    public function test_can_select_mediaFile_from_vault_folder_to_attach_to_post_single_op()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('posts', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $fan = $timeline->followers[0];

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediaFile);
        $mediaFile = MediaFile::find($content->mediaFile->id);
        $this->assertNotNull($mediaFile);
        $this->assertTrue( $vaultFolder->mediaFiles->contains($mediaFile->id) );

        // --- Create a free post with image from vault ---

        //$filename = $this->faker->slug;
        //$file = UploadedFile::fake()->image($filename, 200, 200);
        $payload = [
            'type' => PostTypeEnum::FREE,
            'timeline_id' => $timeline->id,
            'description' => $this->faker->realText,
            'mediaFiles' => [$mediaFile->id],
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
        $mediaFile = $post->mediaFiles->shift();
        $this->assertNotNull($mediaFile, 'No mediaFiles attached to post');

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('posts.show', $post->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediaFiles.show', $mediaFile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group vault
     *  @group regression
     */
    public function test_can_select_mediaFile_from_vault_folder_to_attach_to_story_single_op()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;
        $fan = $timeline->followers[0];

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($owner)->first();
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediaFile);
        $mediaFile = MediaFile::find($content->mediaFile->id);
        $this->assertNotNull($mediaFile);
        $this->assertTrue( $vaultFolder->mediaFiles->contains($mediaFile->id) );

        // --- Create a free story with image from vault ---

        $attrs = [
            'type' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
        ];
        $payload = [
            'attrs' => json_encode($attrs),
            'mediaFile' => $mediaFile->id,
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
        $mediaFile = $story->mediaFiles->shift();
        $this->assertNotNull($mediaFile, 'No mediaFiles attached to story');

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.show', $story->id));
        $response->assertStatus(200);

        $response = $this->actingAs($fan)->ajaxJSON('GET', route('mediaFiles.show', $mediaFile->id));
        $response->assertStatus(200);
    }

    /**
     *  @group vault
     *  @group OFF-regression: this test only makes sense if we pass the timeline_id which the story will be added to
     */
    /*
    public function test_nonowner_can_not_select_mediaFile_from_vault_folder_to_attach_to_story_single_op()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $storyOwner = $timeline->user;
        $mediaFileowner = User::where('id', '<>', $storyOwner->id)->first();

        // --- Upload image to vault ---
        $filename = $this->faker->slug;
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $primaryVault = Vault::primary($mediaFileowner)->first();
        $vaultFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first(); // root

        $payload = [
            'type' => MediaFileTypeEnum::VAULT,
            'mediaFile' => $file,
            'resource_type' => 'vaultFolders',
            'resource_id' => $vaultFolder->id,
        ];
        $response = $this->actingAs($mediaFileowner)->ajaxJSON('POST', route('mediaFiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->mediaFile);
        $mediaFile = MediaFile::find($content->mediaFile->id);
        $this->assertNotNull($mediaFile);
        $this->assertTrue( $vaultFolder->mediaFiles->contains($mediaFile->id) );

        // --- Create a free story with image from vault ---

        $payload = [
            'attrs' => json_encode([ 'type' => StoryTypeEnum::PHOTO, 'content' => $this->faker->realText ]),
            'mediaFile' => $mediaFile->id,
        ];
        $response = $this->actingAs($mediaFileowner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(403);
        $response = $this->actingAs($storyOwner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(403);
    }
     */

    /**
     *  @group vault
     *  @group regression
     *  @group here
     *  %TODO: [ ] test for disallow sharing root folder
     */
    public function test_select_vault_folder_to_share_via_signup_invite_to_non_registered_user_via_email()
    {
        Mail::fake();

        $owner = User::first();
        $primaryVault = Vault::primary($owner)->first();
        $rootFolder = VaultFolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        // make a subfolder
        $payload = [
            'vault_id' => $primaryVault->id,
            'parent_id' => $rootFolder->id,
            'name' => $this->faker->slug,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('vaultFolders.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $this->assertNotNull($content->vaultFolder);
        $subFolder = VaultFolder::find($content->vaultFolder->id);
        $this->assertNotNull($subFolder);
        $rootFolder->refresh();
        //$rootFolder->load('children');

        $invitees = [];
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName;
        $email = strtolower($firstName.'.'.$lastName).'@example.com';
        $invitees[] = [
            'email' => $email,
            'name' => $firstName.' '.$lastName,
        ];
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName;
        $email = strtolower($firstName.'.'.$lastName).'@example.com';
        $invitees[] = [
            'email' => $email,
            'name' => $firstName.' '.$lastName,
        ];

        // share the subfolder
        $payload = [
            'invitees' => $invitees,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('vaultFolders.invite', $subFolder->id), $payload);
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

