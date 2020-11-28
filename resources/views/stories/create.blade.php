@extends('layouts.app2')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <h1 class="">Create Story</h1>
        </div>
    </div>

    {{--
    @include('core-templates::common.errors')
    --}}

    <div class="row">
        <div class="col-sm-12">
          <example></example>
        </div>
        {!! Form::open(['route' => 'timelines.store']) !!}
        {!! Form::close() !!}
    </div>
@endsection
