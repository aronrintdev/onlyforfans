@extends('layouts.app2')

@section('content')
    <div class="row mt-5">
        <div class="col-sm-12">
          <h1 class="">Create Story for {{ $timeline->username }}</h1>
        </div>
    </div>

    {{--
    @include('core-templates::common.errors')
    --}}

    <div class="row">
        <div class="col-sm-12">
          <create-story username="{{$timeline->username}}"></create-story>
        </div>
    </div>
@endsection
