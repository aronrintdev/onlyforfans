<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Notes;
use Illuminate\Http\Request;

class NotesController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'noticed_id' => 'uuid|exists:timelines,id',
            'notes'      => 'string',
        ]);
        $session_user = $request->user();
        $notes = Notes::create([
            'user_id'     => $session_user->id,
            'noticed_id'  => $request->noticed_id,
            'notes'       => $request->notes,
        ]);

        return response()->json(['note' => $notes], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notes  $notes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notes $notes)
    {
        $request->validate([
            'notes'      => 'string',
        ]);
        $update['notes'] = $request->notes;
        $notes->update($update);

        return response()->json(['note' => $notes], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notes  $notes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notes $notes)
    {
        $notes->delete();
        return response()->json(200);
    }
}
