@extends('layouts.app2')

@php
  //dd($stories);
@endphp
@section('content')
    <div class="row mt-5">
        <div class="col-sm-12">
          <story-player username="{{ $sessionUser->username }}" v-bind:stories="{{ $stories }}"></story-player>
        </div>
    </div>
@endsection
