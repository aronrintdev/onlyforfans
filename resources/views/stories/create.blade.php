@extends('layouts.app2')

@section('content')
    {{--
    @include('core-templates::common.errors')
    --}}

    <div id="view-stories_create" class="row">
        <div class="col-sm-12">
          <create-story :dto-user="{{ json_encode($dtoUser) }}" :stories="{{ json_encode($stories) }}"></create-story>
        </div>
    </div>
@endsection
