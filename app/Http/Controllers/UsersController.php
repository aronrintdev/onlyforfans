<?php
namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\User;

class UsersController extends AppBaseController
{

    public function match(Request $request)
    {
        if ( !$request->ajax() ) {
            \App::abort(400, 'Requires AJAX');
        }

        $term = $request->input('term',null);
        $collection = User::where( function($q1) use($term) {
                         //$q1->where('first_name', 'like', $term.'%')->orWhere('last_name', 'like', $term.'%');
                         $q1->where('email', 'like', $term.'%');
                      })
                      //->where('estatus', EmployeeStatusEnum::ACTIVE) // active users only
                      ->get();

        //return \Response::json([ 'collection'=> $collection, ]);
        $field = $request->has('field') ? $request->field : null;

        return response()->json( $collection->map( function($item,$key) use($field) {
            $attrs = [
                    'id' => $item->id,
                    'value' => $item->id,
                    'label' => $field ? $item->{$field} : $item->email,
                    //'value' => $field ? $item->{$field} : $item->slug, // default to username/slug
                    //'label' => $item->renderName(),
                ];
                return $attrs;
        }) );

    }
}
