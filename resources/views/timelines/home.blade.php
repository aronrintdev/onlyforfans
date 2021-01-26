@extends('layouts.app2')

@section('content')
  <home-feed
    :timeline="{{ $timeline }}"
    :session_user="{{ $sessionUser }}"
    ></home-feed>
@endsection
