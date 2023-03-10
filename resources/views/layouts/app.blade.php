<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf_token" content="{!! csrf_token() !!}"/>
        <meta name="csrf-token" content="{!! csrf_token() !!}"/>
        
    <link href="{{ url('/').mix('themes/default/assets/css/style.css', '') }}" rel="stylesheet"/>
    <link href="{{ url('/').mix('themes/default/assets/css/flag-icon.css', '') }}" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>{{ Setting::get('site_title') }}</title>


</head>
<body id="app-layout">
    <nav class="navbar fans navbar-default no-bg guest-nav" style="position: relative; height: 72px">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-4" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
                            <a class="navbar-brand fans" href="{{ url('/') }}" style="padding-top:20px;">
                                {{ Setting::get('site_name') }}
                                {{--<img class="fans-logo" src="{{ asset('images/logo.png') }}" alt="{{ Setting::get('site_name') }}" title="{{ Setting::get('site_name') }}">--}}
                            </a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-4">


            @if (Auth::guest())
            <ul class="nav navbar-nav navbar-right">
{{--                <li class="logout">--}}
{{--                    <a href="{{ url('/register') }}"><i class="fa fa-sign-in" aria-hidden="true"></i> {{ trans('common.join') }}</a>--}}
{{--                </li>--}}
{{--                <li class="logout">--}}
{{--                    <a href="{{ url('/login') }}"><i class="fa fa-unlock" aria-hidden="true"></i> {{ trans('common.signin') }}</a>--}}
{{--                </li>--}}
                @if (Config::get('app.env') == 'demo')
                    <li class="logout">
                        <a href="http://fans-rtl.laravelguru.com" target="_blank">{{ trans('common.rtl_version') }}</a>
                    </li>
                @endif
            </ul>
            @else
            <ul class="nav navbar-nav navbar-right" id="navbar-right">
                    <li class="dropdown user-image fans">
                        <a href="{{ url(Auth::user()->username) }}" class="dropdown-toggle no-padding" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="img-radius img-30" title="{{ Auth::user()->name }}">

                            <span class="user-name">{{ Auth::user()->name }}</span><i class="fa fa-angle-down" aria-hidden="true"></i></a>
                            <ul class="dropdown-menu">
                                @if(Auth::user()->hasRole('admin'))
                                <li class="{{ Request::segment(1) == 'admin' ? 'active' : '' }}"><a href="{{ url('admin') }}"><i class="fa fa-user-secret" aria-hidden="true"></i>{{ trans('common.admin') }}</a></li>
                                @endif
                                <li class="{{ (Request::segment(1) == Auth::user()->username && Request::segment(2) == '') ? 'active' : '' }}"><a href="{{ url(Auth::user()->username) }}"><i class="fa fa-user" aria-hidden="true"></i>{{ trans('common.my_profile') }}</a></li>

                                <li class="{{ Request::segment(2) == 'pages-groups' ? 'active' : '' }}"><a href="{{ url(Auth::user()->username.'/pages-groups') }}"><i class="fa fa-bars" aria-hidden="true"></i>{{ trans('common.my_pages_groups') }}</a></li>

                                <li class="{{ Request::segment(3) == 'general' ? 'active' : '' }}"><a href="{{ url('/'.Auth::user()->username.'/settings/general') }}"><i class="fa fa-cog" aria-hidden="true"></i>{{ trans('common.settings') }}</a></li>

                                <li><a href="{{ url('/logout') }}"><i class="fa fa-unlock" aria-hidden="true"></i>{{ trans('common.logout') }}</a></li>
                            </ul>
                        </li>
                </ul>
                @endif
            </div>
        </div>
    </nav>


    @yield('content')

<div class="modal fade" id="usersModal" tabindex="-1" role="dialog" aria-labelledby="usersModalLabel">
    <div class="modal-dialog modal-likes" role="document">
        <div class="modal-content">
            <i class="fa fa-spinner fa-spin"></i>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="footer-description">

        <div class="modal fade" id="usersModal" tabindex="-1" role="dialog" aria-labelledby="usersModalLabel">
            <div class="modal-dialog modal-likes" role="document">
                <div class="modal-content">
                    <i class="fa fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="footer-description">
                <div class="row" style="margin-bottom: 60px; text-align:center">
                    <span class="col-sm-2 col-2 col-lg-2"></span>
                    <a href="{{url('faq')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.faq') }}</a>
                    <a href="{{url('support')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.support_footer') }}</a>
                    <a href="{{url('terms-of-use')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.term_of_use_footer') }}</a>
                    <a href="{{url('privacy-policy')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.privacy_policy_footer') }}</a>
                    <a href="{{url('privacy-policy')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.dmca') }}</a>
                    <a href="{{url('privacy-policy')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.usc2257') }}</a>
                    <a href="{{url('privacy-policy')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.legal') }}</a>
                    <a href="{{url('privacy-policy')}}" class="col-sm-2 col-2 col-lg-1 col-xs-12" style="margin-bottom:5px">{{ trans('common.blog') }}</a>
                </div>
                <div class="fans-terms text-center" >
                    Copyright &copy; 2020 <a href="{{ url('/') }}">{{ Setting::get('site_title') }}</a>. All rights reserved.
                    <span class="dropup"  style="margin-left: 20px">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
                                                <span>
                                                    <?php
                                                    if (Session::has('my_locale'))
                                                        $key = session('my_locale', 'en');
                                                    else if (Auth::check())
                                                        $key = Auth::user()->language;
                                                    else $key = 'en';
                                                    ?>
                                                    @if($key == 'en')
                                                        <span class="flag-icon flag-icon-us"></span>
                                                    @elseif($key == 'gr')
                                                        <span class="flag-icon flag-icon-gr"></span>
                                                    @elseif($key == 'zh')
                                                        <span class="flag-icon flag-icon-cn"></span>
                                                    @else
                                                        <span class="flag-icon flag-icon-{{ $key }}"></span>
                                                    @endif
                                                </span> <i class="fa fa-angle-down" aria-hidden="true"></i></a>
                    <ul class="dropdown-menu">
                        @foreach( Config::get('app.locales') as $key => $value)
                            <li class=""><a href="#" class="switch-language" data-language="{{ $key }}">
                                    @if($key == 'en')
                                        <span class="flag-icon flag-icon-us"></span>
                                    @elseif($key == 'gr')
                                        <span class="flag-icon flag-icon-gr"></span>
                                    @elseif($key == 'zh')
                                        <span class="flag-icon flag-icon-cn"></span>
                                    @else
                                        <span class="flag-icon flag-icon-{{ $key }}"></span>
                                    @endif
                                    {{ $value }}</a></li>
                        @endforeach
                    </ul></span>
                </div>
                </div>
            </div>
        </div>

    </div>          

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap/bootstrap.min.js"></script>
<script src="../js/bootstrap/app.js"></script>
</body>
</html>
