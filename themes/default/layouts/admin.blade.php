<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="{!! csrf_token() !!}"/>
        <meta name="csrf-token" content="{!! csrf_token() !!}"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height, target-densitydpi=device-dpi" />
        <meta name="keywords" content="{{ Setting::get('meta_keywords') }}">
        <meta name="description" content="{{ Setting::get('meta_description') }}">
        <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.ico')}}">

        <title>{{ Setting::get('site_title') }}</title>

        <link href="{{ asset('/themes/default/assets/css/flag-icon.css') }}" rel="stylesheet">

        {{-- {!! Theme::asset()->styles() !!} --}}
        {{--
        <link href="/themes/default/assets/css/style.0e3cfe20bb993fe353da4e0c3fa3b356.css" rel="stylesheet">
        --}}
        <link href="{{ asset('/themes/default/assets/css/style.0e3cfe20bb993fe353da4e0c3fa3b356.css') }}" rel="stylesheet">

        <script type="text/javascript">
        function SP_source() {
          return "{{ url('/') }}/";
        }

        var base_url = "{{ url('/') }}/";
        var theme_url = "{!! Theme::asset()->url('') !!}";
        var current_username = "{{ Auth::user()->username }}";
        var currentUserId = "{{ Auth::id() }}";

        </script>
        {!! Theme::asset()->scripts() !!}
        @if(Setting::get('google_analytics') != NULL)
            {!! Setting::get('google_analytics') !!}
        @endif
    </head>
    <body @if(Setting::get('enable_rtl') == 'on') class="direction-rtl" @endif>
        {!! Theme::partial('header') !!}

        <div class="page-wrapper">
            <div class="container">
                <div class="row">

                    <div class="col-md-3">
                        {!! Theme::partial('admin-leftbar') !!}
                    </div>

                    <div class="col-md-9">
                        {!! Theme::content() !!}
                    </div>

                </div><!-- /row -->
            </div>
        </div><!-- /amin-content -->
        
        {!! Theme::partial('footer') !!}
        <script>
            var pusherConfig = {
                token: "{{ csrf_token() }}",
                PUSHER_KEY: "{{ config('broadcasting.connections.pusher.key') }}"
            };
       </script>

        {!! Theme::asset()->container('footer')->scripts() !!}
    </body>
</html>