<?php

namespace Database\Factories;

use App\Mediafile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

class MediafileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mediafile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        return [
            'filename'=>(string) Uuid::uuid4(),
            'mimetype' => $file->getMimeType(),
            'orig_filename' => $file->getClientOriginalName(),
            'orig_ext' => $file->getClientOriginalExtension(),
            /*
            'timeline_id' => function () {
                $user = factory(App\User::class)->create();
                return $user->timeline->id;
            },
            */
        ];
    }
}
