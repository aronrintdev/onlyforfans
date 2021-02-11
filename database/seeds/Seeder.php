<?php

namespace Database\Seeders;

use Database\Seeders\Interfaces\EnvironmentCheckable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Seeder as ParentSeeder;

/**
 * Parent Seeder to automatically limit scope of seeders in environment.
 */
class Seeder extends ParentSeeder implements EnvironmentCheckable
{
    /**
     * The environments this seeder should run in. Local and demo only by default.
     * Set to ['all'] to run in all environments.
     */
    protected $environments = [ 'local', 'demo' ];
    // Custom guard: extend the base seeder in there you can set that to all to have the seeder run in all
    // environments including production. By default it is set to local and demo.  So if you have a testing
    // specific seeder you can set it to [ 'testing' ] and it will only run when the environment is testing.
    // Just remember to extend the base seeder to get it to work.

    /**
     * Checks if seeder is allowed to run in current environment
     */
    public function shouldRun(): bool {
        return in_array('all', $this->environments) || in_array(Config::get('app.env'), $this->environments);
    }

    /**
     * Overwrite call function to add environment checking.
     *
     * @param  array|string  $class
     * @param  bool  $silent
     * @param  array  $parameters
     * @return $this
     */
    public function call($class, $silent = false, array $parameters = [])
    {
        $classes = Arr::wrap($class);

        foreach ($classes as $class) {
            $seeder = $this->resolve($class);

            $name = get_class($seeder);

            // Check for Interfaces
            if (!$seeder instanceof EnvironmentCheckable ) {
                throw new InvalidArgumentException($name . ' is not an instance of EnvironmentCheckable. Make sure your seeder extends the Seeder class in /database/seeds and not Illuminate\Database\Seeder');
            }

            // Check for environments property
            if ( ! isset($seeder->environments) ) {
                throw new InvalidArgumentException(
                    '$environments missing from ' .
                    $name .
                    '. Make sure your seeder extends the Seeder class in /database/seeds and not Illuminate\Database\Seeder'
                );
            }

            // Skip if environment not allowed
            if ($seeder->shouldRun() === false) {
                if ($silent === false && isset($this->command)) {
                    $this->command->getOutput()->writeln("<comment>Seeding not allowed in this environment. Seeder:</comment> {$name}");
                    $allowedEnvironments = implode(' | ', $seeder->environments);
                    $this->command->getOutput()->writeln("<comment>   Allowed Environments:</comment> {$allowedEnvironments}");
                }
                continue;
            }

            if ($silent === false && isset($this->command)) {
                $this->command->getOutput()->writeln("<comment>Seeding:</comment> {$name}");
            }

            $startTime = microtime(true);

            $seeder->__invoke($parameters);

            $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

            if ($silent === false && isset($this->command)) {
                $this->command->getOutput()->writeln("<info>Seeded:</info>  {$name} ({$runTime}ms)");
            }
        }

        return $this;
    }
}
