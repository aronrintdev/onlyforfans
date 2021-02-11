<?php

namespace Database\Factories;

use Ramsey\Uuid\Uuid;
use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MediaFile::class;

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
