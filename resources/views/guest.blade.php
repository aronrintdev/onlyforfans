<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        {{-- CSRF TOKEN --}}
        <meta name="csrf_token" content="{!! csrf_token() !!}"/>
        <meta name="csrf-token" content="{!! csrf_token() !!}"/>

        {{-- Site Metadata --}}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height, target-densitydpi=device-dpi" />
        <meta property="og:image" content="{{ url('setting/logo.jpg') }}" />
        <meta property="og:title" content="{{ Setting::get('site_title') }}" />
        <meta property="og:type" content="Social Network" />
        <meta name="keywords" content="{{ Setting::get('meta_keywords') }}">
        <meta name="description" content="{{ Setting::get('meta_description') }}">

        {{-- Fav Icon --}}
        <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.ico')}}">

        {{-- Title --}}
        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Additional Resources --}}
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap"
        rel="stylesheet">

        {{-- Style Sheets --}}
        <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

        {{-- Routing --}}
        @routes()
    </head>
    <body>
        <noscript>This site requires javascript to function correctly. Please enable javascript.</noscript>
        <div id="app"></div>

        {{-- Application JS --}}
        @include('vendorjs')
        <script src="{{ mix('js/app.guest.js') }}"></script>
    </body>
</html>
