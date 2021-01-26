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
        <meta property="og:image" content="{{ url('setting/logo.jpg') }}" />
        <meta property="og:title" content="{{ Setting::get('site_title') }}" />
        <meta property="og:type" content="Social Network" />
        <meta name="keywords" content="{{ Setting::get('meta_keywords') }}">
        <meta name="description" content="{{ Setting::get('meta_description') }}">
        <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.ico')}}">


        <title>{{ Setting::get('site_title') }}</title>
        

        <link href="{{ Theme::asset()->url('css/flag-icon.css') }}" rel="stylesheet">
        <link href="{{ Theme::asset()->url('css/custom.css') }}" rel="stylesheet">
        
        {!! Theme::asset()->styles() !!}

        <script type="text/javascript">
        function SP_source() {
          return "{{ url('/') }}/";
        }
        var base_url = "{{ url('/') }}/";
        var theme_url = "{!! Theme::asset()->url('') !!}";
        </script>
        {!! Theme::asset()->scripts() !!}
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet"> 
    </head>
    <body @if(Setting::get('enable_rtl') == 'on') class="direction-rtl" @endif>
        
        {!! Theme::content() !!}
        
        {!! Theme::partial('footer') !!}

        {!! Theme::asset()->container('footer')->scripts() !!}
    </body>
</html>
