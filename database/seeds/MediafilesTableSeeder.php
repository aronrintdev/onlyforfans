<?php
namespace Database\Seeders;

use Exception;
use App\Libs\FactoryHelpers;
use App\Models\Vault;
use App\Models\Vaultfolder;
use App\Models\Mediafile;
use App\Models\User;
use App\Enums\MediafileTypeEnum;

// NOTE: some mediafiles may already be created, related to posts, etc...this seeder fills in gaps like files
//  stored in the vault, etc
class MediafilesTableSeeder extends Seeder
{
    use SeederTraits;

    protected $doS3Upload = false;

    public function run()
    {
        $this->initSeederTraits('MediafilesTableSeeder');

        // +++ Create ... +++

        $users = User::has('vaultfolders', '>=', 1)->get();
        if ( !$users->count() ) {
            throw new Exception('MediafilesTableSeeder -- No users found who have an associated vaultfolder');
        }

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - MediafilesTableSeeder: loaded ".$users->count()." users...");
        }

        $this->doS3Upload = ( $this->appEnv !== 'testing' );

        $users->each( function($u) {
            static $iter = 1;

            if ( ($iter > 5) && $this->faker->boolean(30) ) {
                return false; // no vault files for this user
            }

            $primaryVault = Vault::primary($u)->firstOrFail();
            $rootFolder = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->firstOrFail();

            $count = $this->faker->numberBetween(1,7);
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $count mediafiles for user ".$u->name." (iter $iter)");
            }

            collect(range(1,$count))->each( function() use(&$u, &$rootFolder) {

                $mf = FactoryHelpers::createImage(
                    $u,
                    MediafileTypeEnum::VAULT, 
                    $rootFolder->id, 
                    $this->doS3Upload
                );
            });

            $iter++;
        });
    }

}
