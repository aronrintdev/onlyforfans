<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- %MARK 20210504.a -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="robots" content="noimageindex, noarchive">
    <meta property="og:site_name" content="{{ Setting::get('site_name') }}" />
    <meta property="og:image" content="{{ Setting::get('site_image') }}" />
    <meta property="og:type" content="{{ Setting::get('site_type') }}" />
    <meta name="description" content="{{ Setting::get('meta_description') }}" />
    <meta name="keywords" content="{{ Setting::get('meta_keywords') }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <!-- %NOTE: this is the *generated* CSS file -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

    <link rel="prefetch" as="image" href="/images/logos/allfans-logo-986x205.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/images/logos/allfans-apple-touch-icon-180x180.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="/images/logos/allfans-apple-touch-icon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="/images/logos/allfans-apple-touch-icon-16x16.png" />
    <!-- Static Data -->
    <script>
        const myUserId = '{{ Auth::user()->id }}';
    </script>
    <script>
        const paymentsDisabled = {{  (Config::get('transactions.disableAll', false) || isset(Auth::user()->settings->cattrs['disable_payments'])) ? 1 : 0 }};
    </script>

    {{-- Routing --}}
    @routes()

</head>
<body>
    <div id="app">
        <main-navbar></main-navbar>
        <div class="container-fluid pt-3">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    @include('vendorjs')
    <script src="{{ mix('js/app.js') }}"></script>

    @if (Config::get('intercom.enabled'))
    {{-- Intercom Code --}}
    {{--
        <script>
            window.intercomSettings = {
                app_id: "{{ Config::get('intercom.appId') }}",
                name: "{{ Auth::user()->username }}",  // Full name
                email: "{{ Auth::user()->email }}",  // Email address
                created_at: "{{ Auth::user()->created_at->timestamp; }}"  // Signup date as a Unix timestamp
            };
        </script>

        <script>
            (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/{{ Config::get("intercom.appId") }}';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
        </script>
    --}}
    @endif

    @include('svg')
</body>
</html>
