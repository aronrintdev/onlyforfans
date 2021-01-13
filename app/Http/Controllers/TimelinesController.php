<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Timeline;
//use App\Enums\PaymentTypeEnum;

class TimelinesController extends AppBaseController
{
    // Display my home timeline
    public function home(Request $request)
    {
        $sessionUser = Auth::user();

        return view('timelines.home', [
            'sessionUser' => $sessionUser,
            //'myVault' => $myVault,
            //'vaultRootFolder' => $vaultRootFolder,
        ]);
    }

}
