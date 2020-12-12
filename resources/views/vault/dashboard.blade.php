@extends('layouts.app2')

@php
  //dd($stories);
@endphp
@section('content')
    <div class="row mt-5">
        <div class="col-sm-12">
          <h2>My Vault</h2>
          <my-vault username="{{ $sessionUser->username }}"></my-vault>
        </div>
    </div>
@endsection
