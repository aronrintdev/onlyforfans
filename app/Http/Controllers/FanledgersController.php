<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\FanLedger;
use App\Models\Post;
use App\Models\Timeline;
use App\Enums\PaymentTypeEnum;
use App\Enums\PostTypeEnum;

class FanLedgersController extends AppBaseController
{
    public function index(Request $request)
    {
        $sessionUser = Auth::user();
        $filters = $request->input('filters', []);

        $query = Post::query();
        foreach ($filters as $f) {
            switch ($f['key']) {
                case 'todo':
                    break;
            }
        }
        $fanLedgers = $query->get();

        return response()->json([
            'fanLedgers' => $fanLedgers,
        ]);
    }
}
