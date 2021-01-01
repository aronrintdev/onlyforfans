<?php

namespace App\Http\Controllers;

use App\UsernameRule;
use Illuminate\Http\Request;

// Todo: Limit modify functions to admin only.
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
        //
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
        //
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
        //
    }

    /**
     * Check a specific usernames validity.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string  $username
     * @return \Illuminate\Http\Response
     */
    public function checkUsername(Request $request, string $username = null)
    {
        //
    }
}
