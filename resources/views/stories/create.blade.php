@extends('layouts.app2')

@section('content')
    {{--
    @include('core-templates::common.errors')
    --}}

    <div class="row mt-5">
        <div class="col-sm-12">
          <create-story :dto-user="{{ json_encode($dtoUser) }}"></create-story>
        </div>
    </div>
@endsection
