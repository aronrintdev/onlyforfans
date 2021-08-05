<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- %MARK 20210504.a -->

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <!-- %NOTE: this is the *generated* CSS file -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

    <!-- Static Data -->
    <script>
        const myUserId = '{{ Auth::user()->id }}';
    </script>
    <script>
        const paymentsDisabled = {{ Config::get('transactions.disableAll', 0) }};
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

    {{-- Intercom Code --}}
    {{--
    <script>
        window.intercomSettings = {
            app_id: "rio7vil9"
        };
    </script>

    <script>
        // We pre-filled your app ID in the widget URL: 'https://widget.intercom.io/widget/rio7vil9'
        (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/rio7vil9';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
    </script>
     --}}

    {{-- Intercom Code for logged in users, needs verification hash to work --}}
    <script>
        window.intercomSettings = {
            app_id: "rio7vil9",
            name: "{{ Auth::user()->username }}",  // Full name
            email: "{{ Auth::user()->email }}",  // Email address
            created_at: "{{ Auth::user()->created_at->timestamp; }}"  // Signup date as a Unix timestamp
        };
    </script>

    <script>
        // We pre-filled your app ID in the widget URL: 'https://widget.intercom.io/widget/rio7vil9'
        (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/rio7vil9';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
    </script>

    @include('svg')
</body>
</html>
