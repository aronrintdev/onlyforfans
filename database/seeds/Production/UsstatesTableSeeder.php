<?php
namespace Database\Seeders\Production;

use DB;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
        
use Carbon\Carbon;
use App\Models\Usstate;

class UsstatesTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $sdata = [
                [ 'state_name'=>'Alabama', 'state_code'=>'AL', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Alaska', 'state_code'=>'AK', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Arizona', 'state_code'=>'AZ', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Arkansas', 'state_code'=>'AR', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'California', 'state_code'=>'CA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Colorado', 'state_code'=>'CO', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Connecticut', 'state_code'=>'CT', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Delaware', 'state_code'=>'DE', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Florida', 'state_code'=>'FL', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Georgia', 'state_code'=>'GA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Hawaii', 'state_code'=>'HI', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Idaho', 'state_code'=>'ID', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Illinois', 'state_code'=>'IL', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Indiana', 'state_code'=>'IN', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Iowa', 'state_code'=>'IA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Kansas', 'state_code'=>'KS', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Kentucky', 'state_code'=>'KY', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Louisiana', 'state_code'=>'LA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Maine', 'state_code'=>'ME', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Maryland', 'state_code'=>'MD', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Massachusetts', 'state_code'=>'MA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Michigan', 'state_code'=>'MI', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Minnesota', 'state_code'=>'MN', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Mississippi', 'state_code'=>'MS', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Missouri', 'state_code'=>'MO', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Montana', 'state_code'=>'MT', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Nebraska', 'state_code'=>'NE', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Nevada', 'state_code'=>'NV', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'New Hampshire', 'state_code'=>'NH', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'New Jersey', 'state_code'=>'NJ', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'New Mexico', 'state_code'=>'NM', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'New York', 'state_code'=>'NY', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'North Carolina', 'state_code'=>'NC', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'North Dakota', 'state_code'=>'ND', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Ohio', 'state_code'=>'OH', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Oklahoma', 'state_code'=>'OK', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Oregon', 'state_code'=>'OR', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Pennsylvania', 'state_code'=>'PA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Rhode Island', 'state_code'=>'RI', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'South Carolina', 'state_code'=>'SC', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'South Dakota', 'state_code'=>'SD', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Tennessee', 'state_code'=>'TN', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Texas', 'state_code'=>'TX', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Utah', 'state_code'=>'UT', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Vermont', 'state_code'=>'VT', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Virginia', 'state_code'=>'VA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Washington', 'state_code'=>'WA', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'West Virginia', 'state_code'=>'WV', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Wisconsin', 'state_code'=>'WI', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Wyoming', 'state_code'=>'WY', 'country'=>'USA', 'stype'=>'state' ],
                [ 'state_name'=>'Washington DC', 'state_code'=>'DC', 'country'=>'USA', 'stype'=>'capitol' ],
        ];

        foreach ($sdata as $i => $obj) {
            Usstate::create($obj);
        }
    }
}

