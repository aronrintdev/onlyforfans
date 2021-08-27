<?php
namespace App\Models\Traits;

use App\Libs\UuidGenerator;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid as GoldSpecDigitalUuid;

trait UsesUuid
{
    use GoldSpecDigitalUuid;

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected function generateUuid(): string
    {
        if ( $this->forceCombV4Uuid !== true && Config::get('database.uuid.useTrueRandom') ) {
            return (string) Str::uuid();
        } else {
            return UuidGenerator::generateCombV4Uuid(); // production
        }
    }

}
