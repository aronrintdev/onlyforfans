@extends('layouts.app2')

@section('content')

  <show-feed
    :timeline="{{ $timeline }}"
    :session_user="{{ $sessionUser }}"
    ></show-feed>

@endsection
