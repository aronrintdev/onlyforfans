<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lists;
use \Illuminate\Database\QueryException;

class ListsController extends Controller
{
    public function __construct(Request $request)
    {  
        $this->request = $request;
        $this->middleware('auth');
    }
    public function index()
    {
        return Lists::with(['creator', 'users'])->get();
    }
    public function store(Request $request)
    {
        $sessionUser = $request->user();

        try {
            $list = $sessionUser->userLists()->create([
                'name' => $request->input('name'),
            ]);
            return $list;
        } catch(QueryException $e){
            $errorCode = $e->errorInfo[1];
            if($errorCode == '1062'){
                return response()->json(['error' => 'Duplicate Entry'], 400);
            }
        }
    }
    public function addUserToList(Request $request, $id)
    {
        $sessionUser = $request->user();
        $userId = $request->input('user');

        try {
            $list = Lists::where('id', $id)->first();
            $list->users()->syncWithoutDetaching([$userId]);
            return Lists::with(['creator', 'users'])
                ->where('id', $id)->first();
        } catch(QueryException $e){
            $errorCode = $e->errorInfo[1];
            var_dump($errorCode);
            if($errorCode == '1062'){
                return response()->json(['error' => 'User Not Found'], 404);
            }
        }
    }
    public function removeUserFromList(Request $request, $id, $userId)
    {
        $sessionUser = $request->user();

        try {
            $list = Lists::where('id', $id)->first();
            $list->users()->detach($userId);
            return Lists::with(['creator', 'users'])
                ->where('id', $id)->first();
        } catch(QueryException $e){
            $errorCode = $e->errorInfo[1];
            var_dump($errorCode);
            if($errorCode == '1062'){
                return response()->json(['error' => 'User Not Found'], 404);
            }
        }
    }
}
