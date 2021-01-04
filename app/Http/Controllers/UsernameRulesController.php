<?php

namespace App\Http\Controllers;

use App\UsernameRule;
use Illuminate\Http\Request;
use Validator;

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
        throw new NotImplementedException();
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
        throw new NotImplementedException();
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
        throw new NotImplementedException();
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
     * @param  \App\UsernameRule  $usernameRule
     * @return \Illuminate\Http\Response
     */
    public function show(UsernameRule $usernameRule)
    {
        //
        throw new NotImplementedException();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \App\UsernameRule  $usernameRule
     * @return \Illuminate\Http\Response
     */
    public function edit(UsernameRule $usernameRule)
    {
        //
        throw new NotImplementedException();
    }

    /**
     * Update the specified resource in storage.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UsernameRule  $usernameRule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UsernameRule $usernameRule)
    {
        $validator = Validator::make($request->all(), UsernameRule::validationRules());
        if ($validator->fails()) {
            return redirect('usernameRules.edit')->withErrors($validator)->withInput();
        }
        $usernameRule->fill($request->all());
        $rule->added_by = \Auth::user()->id;
        $usernameRule->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * Middleware: ['auth', 'role:admin']
     *
     * @param  \App\UsernameRule  $usernameRule
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
    public function checkUsername(\App\Http\Requests\CheckUsername $request)
    {
        $validated = $request->validated();

        // Check validity
        if ($ruleCaught = UsernameRule::check($validated->username)) {
            return response()->json([
                'valid' => false,
                'message' => $ruleCaught->explanation,
            ]);
        }
        return response()->json(['valid' => true]);
    }
}
