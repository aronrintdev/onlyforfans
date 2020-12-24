@extends('layouts.app2')

@php
  //dd($stories);
@endphp
@section('content')
    <div id="view-stories_player" class="row">
        <div class="col-sm-12">
          <story-player username="{{ $sessionUser->username }}" v-bind:stories="{{ $stories }}"></story-player>
        </div>
    </div>
@endsection
