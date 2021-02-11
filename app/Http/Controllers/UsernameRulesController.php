<?php

namespace App\Http\Controllers;

use Exception;
use Validator;
use App\Models\User;
use App\Models\UsernameRule;
use Illuminate\Http\Request;
use App\Http\Requests\CheckUsername;
use Illuminate\Support\Facades\Auth;

class UsernameRulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Middleware: `['auth', 'role:admin']`
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        throw new Exception('Not Implement');
    }

    /**
     * Get a listing of the resources.
     *
     * Middleware: `['auth', 'role:admin']`
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $page
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request, string $page)
    {
        //
        throw new Exception('Not Implement');
    }

    /**
     * Show the form for creating a new resource.
     *
     * Middleware: `['auth', 'role:admin']`
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        throw new Exception('Not Implement');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), UsernameRule::validationRules());
        if ($validator->fails()) {
            return redirect('usernameRules.create')->withErrors($validator)->withInput();
        }

        $rule = UsernameRule::create($request->all());
        $rule->added_by = \Auth::user()->id;
        $rule->save();
    }

    /**
     * Display the specified resource.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \App\Models\UsernameRule  $usernameRule
     * @return \Illuminate\Http\Response
     */
    public function show(UsernameRule $usernameRule)
    {
        //
        throw new Exception('Not Implement');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \App\Models\UsernameRule  $usernameRule
     * @return \Illuminate\Http\Response
     */
    public function edit(UsernameRule $usernameRule)
    {
        //
        throw new Exception('Not Implement');
    }

    /**
     * Update the specified resource in storage.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UsernameRule  $usernameRule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UsernameRule $usernameRule)
    {
        $validator = Validator::make($request->all(), UsernameRule::validationRules());
        if ($validator->fails()) {
            return redirect('usernameRules.edit')->withErrors($validator)->withInput();
        }
        $usernameRule->fill($request->all());
        $usernameRule->added_by = Auth::user()->id;
        $usernameRule->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \App\Models\UsernameRule  $usernameRule
     * @return \Illuminate\Http\Response
     */
    public function destroy(UsernameRule $usernameRule)
    {
        $usernameRule->delete();
    }

    /**
     * Check a specific usernames validity.
     *
     * @param  \App\Http\Requests\CheckUsername $request
     * @return \Illuminate\Http\Response
     */
    public function checkUsername($request)
    {
        $validated = $request->validated();

        // Check if in use
        if (User::where('username', $validated['username'])->exists()) {
            return response()->json([
                'valid' => false,
                'message' => trans('username.already_in_use'),
            ]);
        }

        // Check rules
        if ($ruleCaught = UsernameRule::check($validated['username'])) {
            return response()->json([
                'valid' => false,
                'message' => $ruleCaught->explanation,
            ]);
        }

        return response()->json(['valid' => true]);
    }
}
