<?php
namespace Tests\Feature;

//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

//use UsstatesTableSeeder;
//use CategoriesTableSeeder;
//use MilestonesTableSeeder;

//use App\Models\User;
//use App\Models\Mediafile;
//use App\Enums\AccounttypeEnum;


// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
class MedifileTest extends TestCase
{
    //private $admin;

    /**
     *  @group mfdev
     */
    public function test_can_upload_image_file()
    {
        $user = factory(\App\User::class)->make();
        $story = factory(\App\Story::class)->make();

        //$file = UploadedFile::fake()->image('file-foo.png', 400, 400);
        $file = UploadedFile::fake()->image('file-foo.png', 400, 400);
        $payload = [
            'mediafile' => $file,
            'mftype' => 'test',
            'resource_id'=>$story->id,
            'resource_type'=>'stories',
        ];
        //$url = route('mediafiles.store');
        $response = $this->actingAs($user)->json('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(200);

        //dd($response['cart']->toArray());
    }

    /*
    protected function setUp() : void
    {
        parent::setUp();

        $this->seed(UsstatesTableSeeder::class);

        $this->admin = factory(User::class)->create([
            'atype' => AccounttypeEnum::ADMIN,
        ]);

        $category = Category::first();
        $milestone = Milestone::first();

        $products = factory(Product::class, 10)->create([
            'category_id' => $category->id,
            'milestone_id' => $milestone->id,
        ]);

        //$this->setupKits();
    }
    */
}

