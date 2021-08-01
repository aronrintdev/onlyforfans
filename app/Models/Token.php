<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $for
 * @property string $token
 * @property string $used_by_id | User id of the user that used this token
 * @property Carbon $used_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 *
 * @package App\Models
 */
class Token extends Model
{
    use SoftDeletes;

    protected $table = 'tokens';

    protected $guarded = [];

    protected $casts = [
        'custom_attributes' => 'collection',
    ];

    protected $dates = [
        'used_at',
        'expires_at',
    ];

    protected const maxTokenGenerationAttempts = 10;


    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by_id');
    }

    /* ---------------------------------------------------------------------- */
    /*                                Functions                               */
    /* ---------------------------------------------------------------------- */

    public static function add($for, $amount = 1, $options = []): Collection
    {
        $tokens = new Collection([]);
        for ($i = 0; $i <= $amount; $i++) {
            $token = Token::create([
                'token' => Token::generateToken($options['length'] ?? 32, $options['removeSimilarCharacters'] ?? true),
                'for' => $for,
            ]);
            if (isset($options['expires_at'])) {
                $token->expires_at = $options['expires_at'];
                $token->save();
            }
            $tokens->push($token);
        }

        return $tokens;
    }

    public static function check($token, $for = '')
    {
        $token = Token::where('token', $token)
            ->where('for', $for)
            ->whereNull('used_at')
            ->where(function ($query) {
                $query->where('expires_at', '>', Carbon::now())
                    ->orWhereNull('expires_at');
            })->first();
        return $token ?? false;
    }

    public static function useToken($token, User $user, $for = '')
    {
        $token = Token::check($token, $for);
        if ($token) {
            return $token->use($user);
        }
        return false;
    }

    public function use(User $user)
    {
        $this->used_at = Carbon::now();
        $this->used_by_id = $user->id;
        $this->save();
        return $this;
    }

    public static function generateToken(int $length = 32, bool $removeSimilarCharacters = true)
    {
        $attempts = 0;
        $token = '';
        $maxAttempts = static::maxTokenGenerationAttempts;
        do {
            $attempts++;
            $token = '';
            $bytesWithMargin = random_bytes($length * 3);
            $base64 = base64_encode($bytesWithMargin);
            $purified = preg_replace("/[+=\/.]/", "", $base64);
            if ($removeSimilarCharacters) {
                $purified = preg_replace("/[I1l0Oo]/", "", $purified);
            }
            $token = substr($purified, 0, $length);
            // If token exist try again
            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException("Failed to generate token that doesnt exist already $maxAttempts or more times");
            }
        } while (Token::where('token', $token)->exists() && $attempts < 10);

        return $token;
    }


}
