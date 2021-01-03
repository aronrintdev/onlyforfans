<?php

namespace App;

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

    /** Hidden attributes */
    protected $hidden = [ 'added_by', ];

    /**
     * Check if the this username value is valid.
     *
     * @return UsernameRule - The rule that was caught.
     * @return bool - returns false if no rules were triggered.
     */
    public static function check($value)
    {
        // Check if username is already in use
        if (Timeline::where('username', $value)->exists()) {
            return [
                'explanation' => __('username.already_in_use'),
            ];
        }

        // Check word rules first, this is the fastest check.
        $caught = UsernameRule::where('type', 'blacklist')
            ->where('comparison_type', 'word')
            // strtolower to avoid capitalization any nonsense
            ->where('rule', strtolower($value))
            ->first();
        if ($caught) {
            return UsernameRule::localize($caught);
        }

        // Run through regex rules next, they are more computationally intensive.
        $caught = UsernameRule::where('type', 'blacklist')
            ->where('comparison_type', 'regex')
            ->whereRaw('? regexp rule', [$value])
            ->first();
        if ($caught) {
            return UsernameRule::localize($caught);
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
    public static function create_random($rule = null, $faker = null)
    {
        if ($faker === null) {
            $faker = Faker::create();
        }
        if (!$rule) {
            $rule = config('users.generatedUsernameTemplate');
        }
        do {
            $username = $faker()->bothify($rule);
        } while (Timeline::where('username')->count() > 0);
        return $username;
    }

    /**
     * Localizes rule explanation for end user
     */
    public static function localize($rule)
    {
        if ($rule->explanation) {
            $caught->explanation = __('username.custom.' . $rule->explanation) ?? $rule->explanation;
        } else {
            // Priority: custom named after rule => default wording for rule => invalid
            $rule->explanation = __('username.custom.' . $rule->rule) ??
                __('username.default.' . $rule->comparison_type, ['rule' => $rule->rule]) ??
                __('username.invalid');
        }
        return $rule;
    }

    /**
     * Relationships
     *
     */

    /** Added by admin */
    public function addedBy() {
        return $this->hasOne('App\User', 'added_by');
    }

}
