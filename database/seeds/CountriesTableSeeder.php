<?php
namespace Database\Seeders;

use DB;
//use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
        
use Carbon\Carbon;
use App\Models\Country;

class CountriesTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        //Model::unguard();
        DB::table('countries')->delete();

        $sdata = [
                [ 'country_name'=>'Canada', 'country_code'=>'CA', 'ctype'=>'country' ],
                [ 'country_name'=>'New Zealand', 'country_code'=>'NZ', 'ctype'=>'country' ],
                [ 'country_name'=>'United States', 'country_code'=>'US', 'ctype'=>'country' ],
                [ 'country_name'=>'Japan', 'country_code'=>'JP', 'ctype'=>'country' ],
                [ 'country_name'=>'Germany', 'country_code'=>'DE', 'ctype'=>'country' ],
                [ 'country_name'=>'Spain', 'country_code'=>'SP', 'ctype'=>'country' ],
        ];

        foreach ($sdata as $i => $obj) {
            Country::create($obj);
        }
        //Model::reguard();
    }
}

