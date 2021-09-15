<?php
namespace App\Models;

use DB;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Model;

/**
 * Username blacklist and whitelist rule set.
 */
class UsernameRule extends Model
{
    /**
     * Model ref:
     * - id              | int
     * - rule            | rule word or regex
     * - type            | `blacklist`, `whitelist`, or `approval`
     * - comparison_type | `word` or `regex`
     * - explanation     | Common language explanation for end user if rule is invoked, This can be a localization item
     *                        added to `username.custom` localizations.
     * - added_by        | Admin that added rule, null for system/list added.
     * - (timestamps)
     */

    /** Table name */
    protected $table = 'username_rules';

    /** Guarded attributes */
    protected $guarded = [
        'id',
        'added_by',
    ];

    /** Hidden attributes */
    protected $hidden = [ 'added_by' ];

    /**
     * Validator Rules
     */
    public static function validationRules()
    {
        return [
            'rule' => 'required',
            'type' => [
                'sometimes',
                function ($value) {
                    return \in_array($value, ['blacklist', 'whitelist', 'approval']);
                }
            ],
            'comparison_type' => [
                'sometimes',
                function ($value) {
                    return \in_array($value, ['word', 'regex']);
                }
            ],
        ];
    }

    /**
     * Check if the this username value is valid.
     *
     * @return UsernameRule - The rule that was caught.
     * @return bool - returns false if no rules were triggered.
     */
    public static function check($value)
    {
        // Check word rules first, this is the fastest check.
        $caught = UsernameRule::where('type', 'blacklist')
            ->where('comparison_type', 'word')
            ->where('rule', strtolower($value)) // strtolower to avoid capitalization any nonsense
            ->first();

        if ($caught) {
            return UsernameRule::localize($caught);
        }

        // Run through regex rules next, they are more computationally intensive.
        if ( !DB::Connection() instanceof \Illuminate\Database\SQLiteConnection ) {
            $caught = UsernameRule::where('type', 'blacklist')
                ->where('comparison_type', 'regex')
                ->whereRaw('? regexp rule', [$value])
                ->first();

            if ($caught) {
                return UsernameRule::localize($caught);
            }
        }

        return false;
    }

    /**
     * Create a new random username, and verify no conflicts.
     * Uses a Faker `bothify` string to generate.
     * Rule replaces `#` with numbers, `?` with letters, and `*` with number or letter
     *
     * @param string $rule - bothify rule, defaults to value set in config `users.generatedUsernameTemplate`
     * @param \Faker\Factory $faker - Faker instance if you want to seed or not create new instance
     * @return string - non conflicting username.
     */
    public static function createRandom($rule = null, $faker = null)
    {
        if ($faker === null) {
            $faker = Faker::create();
        }
        if (!$rule) {
            $rule = config('users.generatedUsernameTemplate');
        }
        do {
            $username = $faker->bothify($rule);
        } while (User::where('username')->count() > 0);
        return $username;
    }

    /**
     * Localizes rule explanation for end user
     */
    public static function localize($rule)
    {
        if ($rule->explanation) {
            $rule->explanation = __('username.custom.' . $rule->explanation);
            if ($rule->explanation === 'username.custom.' . $rule->explanation) {
                $rule->explanation = __($rule->explanation);
            }
        } else {
            // Priority: custom named after rule => default wording for rule => invalid
            $rule->explanation = __('username.custom.' . $rule->rule);
            if ($rule->explanation == 'username.custom.' . $rule->rule) {
                $rule->explanation = __('username.default.' . $rule->comparison_type, ['rule' => $rule->rule]);
                if ($rule->explanation == 'username.default.' . $rule->comparison_type) {
                    $rule->explanation = __('username.invalid');
                }
            }
        }
        return $rule;
    }

    /**
     * Relationships
     *
     */

    /**
     * User that added the rule
     */
    public function addedBy()
    {
        return $this->hasOne(User::class, 'added_by');
    }
}
