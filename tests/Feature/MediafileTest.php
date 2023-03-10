<?php
namespace Tests\Feature;

use DB;
use Exception;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Database\Seeders\TestDatabaseSeeder;

use Tests\TestCase;

use App\Libs\FactoryHelpers;
use App\Models\User;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\Post;
use App\Models\Mediafile;
use App\Models\Vaultfolder;
use App\Enums\MediafileTypeEnum;

// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
// https://stackoverflow.com/questions/34455410/error-executing-putobject-on-aws-upload-fails
class MediafileTest extends TestCase
{
    use WithFaker;
    //use RefreshDatabase;

    /**
     *  @group mediafiles
     *  @group regression
     *  @group regression-base
     */
    public function test_admin_can_list_mediafiles()
    {

        $admin = User::first();
        $admin->assignRole('super-admin'); // upgrade to admin!
        $admin->refresh();

        $response = $this->actingAs($admin)->ajaxJSON('GET', route('mediafiles.index'), [
            'mftype' => MediafileTypeEnum::AVATAR,
        ]);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $admin->removeRole('super-admin'); // revert (else future tests will fail)
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertObjectHasAttribute('mfname', $content->data[0]);
        $this->assertObjectHasAttribute('mftype', $content->data[0]);
        $this->assertEquals(MediafileTypeEnum::AVATAR, $content->data[0]->mftype);
    }

    /**
     *  @group mediafiles
     *  @group regression
     *  @group regression-base
     */
    public function test_owner_can_list_mediafiles()
    {
        $owner = User::has('posts.mediafiles', '>=', 1)
            ->has('timeline.stories.mediafiles', '>=', 1)
            ->firstOrFail();

        $response = $this->actingAs($owner)->ajaxJSON('GET', route('mediafiles.index'), [
            //'mftype' => MediafileTypeEnum::AVATAR,
        ]);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        //$this->assertEquals($expectedCount, count($content->data));
        $this->assertObjectHasAttribute('mfname', $content->data[0]);
        $this->assertObjectHasAttribute('mftype', $content->data[0]);
        //$this->assertEquals(MediafileTypeEnum::AVATAR, $content->data[0]->mftype);

        // All resources returned are owned 
        $ownedCount = collect($content->data)->reduce( function($acc, $item) use(&$owner) {
            switch ( $item->resource_type ) {
            case 'posts':
                $resource = Post::findOrFail($item->resource_id);
                if ( $resource->getPrimaryOwner()->id === $owner->id ) {
                    $acc += 1;
                }
                break;
            case 'stories':
                $resource = Story::findOrFail($item->resource_id);
                if ( $resource->getPrimaryOwner()->id === $owner->id ) {
                    $acc += 1;
                }
                break;
            case 'users':
                $resource = User::findOrFail($item->resource_id);
                if ( $resource->id === $owner->id ) {
                    $acc += 1;
                }
                break;
            case 'vaultfolders':
                $resource = Vaultfolder::findOrFail($item->resource_id);
                if ( $resource->getPrimaryOwner()->id === $owner->id ) {
                    $acc += 1;
                }
                break;
            default:
                throw new Exception('Unknown resource_type: '.$item->resource_type);
            }
            return $acc;
        }, 0);
        $this->assertEquals(count($content->data), $ownedCount); 
    }

    /**
     *  @group mediafiles
     *  @group regression
     *  @group regression-base
     */
    public function test_owner_can_list_mediafiles_in_vault()
    {
        $owner = User::has('vaultfolders.mediafiles', '>=', 1)->firstOrFail();

        $response = $this->actingAs($owner)->ajaxJSON('GET', route('mediafiles.index'), [
            'mftype' => MediafileTypeEnum::VAULT,
            'take' => 1000, // should be more than enough (test DB is small)
        ]);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $this->assertEquals(1, $content->meta->current_page);

        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        //$this->assertEquals($expectedCount, count($content->data));
        $this->assertObjectHasAttribute('mfname', $content->data[0]);
        $this->assertObjectHasAttribute('mftype', $content->data[0]);
        $this->assertEquals(MediafileTypeEnum::VAULT, $content->data[0]->mftype);

        // All resources returned are owned and of mftype 'vault'
        $ownedCount = collect($content->data)->reduce( function($acc, $item) use(&$owner) {
            if ( $item->resource_type==='vaultfolders' && $item->mftype===MediafileTypeEnum::VAULT ) {
                $acc += 1;
            }
            return $acc;
        }, 0);
        $this->assertEquals(count($content->data), $ownedCount); 
    }

    /**
     *  @group mediafiles
     *  @group regression
     *  @group regression-base
     *  @group june0624
     */
    public function test_can_store_mediafile()
    {
        Storage::fake('s3');
        $filename = 'file-foo.png';

        $user = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'mediafile' => $file,
            'mftype' => MediafileTypeEnum::AVATAR,
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('mediafiles.store'), $payload);

        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = $content->mediafile;

        Storage::disk('s3')->assertExists($mediafile->diskmediafile->filepath);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame(MediafileTypeEnum::AVATAR, $mediafile->mftype);
        $this->assertNotNull($mediafile->diskmediafile->orig_size);
        $this->assertGreaterThan(0, $mediafile->diskmediafile->orig_size);
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        //$this->seed(TestDatabaseSeeder::class);

        // Update or add avatars to some users for this test...
        $users = User::take(5)->get();
        $users->each( function($u) {
            $avatar = FactoryHelpers::createImage($u, MediafileTypeEnum::AVATAR, $u->id, false); //skip S3 upload
            $u->save();
        });
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

