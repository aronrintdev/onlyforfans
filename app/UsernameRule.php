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
     * - explication     | English explication for end user if rule is invoked
     * - added_by        | Admin that added rule, null for system/list added.
     * - (timestamps)
     */

     /** Table name */
    protected $table = 'username_rules';

    /** Lazy Loaded faker factory */
    private $faker = null;

    /**
     * Faker seeding for unit testing
     * @return UsernameRule - new instance of UsernameRule with seeded faker instance
     */
    public static function seed($seed) {
        $instance = new UsernameRule();
        $instance->faker($seed);
        return $instance;
    }

    /**
     * Get faker instance
     * @return Faker
     */
    function faker($seed = null) {
        if ($this->faker === null) {
            $this->faker = Faker::create();
        }
        if ($seed !== null) {
            $this->faker->seed($seed);
        }
        return $this->faker;
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
            ->where('rule', $value)
            ->first();
        if ($caught) {
            return $caught;
        }

        // Run through regex rules next, they are more computationally intensive.
        $caught = UsernameRule::where('type', 'blacklist')
            ->where('comparison_type', 'regex')
            ->whereRaw('? regexp rule', [$value])
            ->first();
        if ($caught) {
            return $caught;
        }
        return false;
    }

    /**
     * Create a new random username, and verify no conflicts.
     * Uses a Faker `bothify` string to generate.
     * Rule replaces `#` with numbers, `?` with letters, and `*` with number or letter
     *
     * @param string $rule - bothify rule, defaults to value set in config `users.generatedUsernameTemplate`
     * @return string - non conflicting username.
     */
    public function create_random($rule = null)
    {
        if (!$rule) {
            $rule = config('users.generatedUsernameTemplate');
        }
        do {
            $username = $this->faker()->bothify($rule);
        } while (Timeline::where('username')->count() > 0);
        return $username;
    }

}
