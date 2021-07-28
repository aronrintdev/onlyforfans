<?php
namespace Database\Seeders\Production;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Seeder;
use Symfony\Component\Finder\Finder;

use App\Models\UsernameRule;

/**
 * Seeds the UsernameRules with initial rule sets.
 */
class UsernameRulesSeeder extends Seeder
{
    public function run()
    {
        $appEnv = Config::get('app.env');
        $this->command->info('Running Seeder: UsernameRulesSeeder, env: '.$appEnv.' ...');

        // Regex Rules
        foreach($this->regexRules() as $rule) {
            $usernameRule = UsernameRule::firstOrNew([
                'rule' => $rule,
                'comparison_type' => 'regex',
            ]);
            $usernameRule->rule = $rule;
            $usernameRule->type = 'blacklist';
            $usernameRule->save();
        }

        // Word Rules
        foreach($this->wordRules() as $rule) {
            $usernameRule = UsernameRule::firstOrNew([
                'rule' => $rule,
                'comparison_type' => 'word',
            ]);
            $usernameRule->rule = $rule;
            $usernameRule->type = 'blacklist';
            $usernameRule->save();
        }
    }

    public static function regexRules() {
        $rules = [
            /**
             * Additional quick regex restrictions can go here.
             * Do not include regex slashes `/`
             */
        ];
        $files = Finder::create()
            ->in(base_path('database/seeds/data/usernameRules/regex'))
            ->name('*.php');
        foreach($files as $file) {
            $additionalRules = include $file->getRealPath();
            $rules = array_merge($rules, $additionalRules);
        }
        return $rules;
    }

    /**
     * Collection of word rules to add
     * @return array
     */
    public static function wordRules() {
        $rules = [
            /**
             * Additional restrictions go here for words
             */
        ];
        $files = Finder::create()
            ->in(base_path('database/seeds/data/usernameRules/words'))
            ->name('*.php');
        foreach($files as $file) {
            $additionalRules = include $file->getRealPath();
            $rules = array_merge($rules, $additionalRules);
        }
        return $rules;
    }

}
