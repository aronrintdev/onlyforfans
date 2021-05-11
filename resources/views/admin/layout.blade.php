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
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Routing -->
    @routes('admin')
</head>
<body>
    <div id="app">
        {{-- <main-navbar></main-navbar> --}}
        <div class="container-fluid pt-3">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    @include('vendorjs')
    <script src="{{ mix('js/admin.app.js') }}"></script>
</body>
</html>

