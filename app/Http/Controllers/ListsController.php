<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lists;
use \Illuminate\Database\QueryException;
use function _\orderBy;
use function _\map;

class ListsController extends Controller
{
    public function __construct(Request $request)
    {  
        $this->request = $request;
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $sortBy = $request->query('sort');
        $sortDir = $request->query('dir');
        if (!$sortDir) {
            $sortDir = 'asc';
        }
        if ($sortBy === 'name') {
            $lists = Lists::with(['creator', 'users'])
                ->orderBy('name', $sortDir)
                ->get();
        } else if ($sortBy === 'recent') {
            $lists = Lists::with(['creator', 'users'])
                ->orderBy('created_at', $sortDir)
                ->get();
        } else if ($sortBy === 'people') {
            $lists = Lists::with(['creator', 'users'])->get()->makeVisible(['user']);
            $lists->each(function ($list) {
                if (!$list->users) {
                    $list->users = [];
                }
                $list->people = sizeof($list->users);
            });
            $lists = orderBy($lists, ['people', 'created_at'], [$sortDir, 'asc']);
            $lists = map($lists, function ($list) {
                return $list['value'];
            });
        } else {
            $lists = Lists::with(['creator', 'users'])
                ->get();
        }
        return $lists;
    }
    public function store(Request $request)
    {
        $sessionUser = $request->user();

        try {
            $list = $sessionUser->userLists()->create([
                'name' => $request->input('name'),
            ]);
            $list->users = [];
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
    public function addToPin(Request $request, $id)
    {
        $list = Lists::where('id', $id)->first();
        $list->isPinned = true;
        $list->save();
        return ['status' => 'success'];
    }
    public function removeFromPin(Request $request, $id)
    {
        $list = Lists::where('id', $id)->first();
        $list->isPinned = false;
        $list->save();
        return ['status' => 'success'];
    }
}
