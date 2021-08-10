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
        <meta property="og:title" content="{{ Setting::get('site_title') }}" />
        <meta name="robots" content="noimageindex, noarchive">
        <meta property="og:site_name" content="{{ Setting::get('site_name') }}" />
        <meta property="og:image" content="{{ Setting::get('site_image') }}" />
        <meta property="og:type" content="{{ Setting::get('site_type') }}" />
        <meta name="description" content="{{ Setting::get('meta_description') }}" />
        <meta name="keywords" content="{{ Setting::get('meta_keywords') }}" />

        {{-- Fav Icon --}}
        <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.ico')}}">

        {{-- Title --}}
        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Additional Resources --}}
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap"
        rel="stylesheet">

        {{-- Style Sheets --}}
        <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/logos/allfans-apple-touch-icon-180x180.png" />
        <link rel="icon" type="image/png" sizes="32x32" href="/images/logos/allfans-apple-touch-icon-32x32.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="/images/logos/allfans-apple-touch-icon-16x16.png" />

        {{-- Routing --}}
        @routes()
    </head>
    <body>
        <noscript>This site requires javascript to function correctly. Please enable javascript.</noscript>
        <div id="app"></div>

        {{-- Application JS --}}
        @include('vendorjs')
        <script src="{{ mix('js/app.guest.js') }}"></script>

        @include('svg')
    </body>
</html>
