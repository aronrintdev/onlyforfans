@extends('layouts.app2')

@php
  //dd($stories);
@endphp
@section('content')
  <div class="row mt-5">
    <div class="col-sm-12">
      <my-vault 
         username="{{ $sessionUser->username }}" 
         :mediafiles="{{ json_encode($mediafiles) }}" 
         :cwf="{{ $cwf }}" 
         :parent="{{ json_encode($parent) }}"
         ></my-vault>
    </div>
  </div>
{{--
         :children="{{ json_encode($children) }}"
--}}
@endsection
