<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script>
      window.currentUserId = {{ $user->id }};
    </script>
</head>
<body>
    <div id="app">
        {{-- @include('common._header') --}}
        <div class="container-fluid pt-3">
        @yield('content')
        <div id="user-cover">
          <online-status :user="{{ json_encode($user->only(['id', 'last_logged'])) }}"></online-status>
        </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.onlineMonitor.js') }}"></script>
    <script src="{{ asset('js/app.userProfile.js') }}"></script>
</body>
</html>