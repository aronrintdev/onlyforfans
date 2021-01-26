<!DOCTYPE html>
<html lang="es">
    <!-- %VIEW: public/themes/default/layout/default.blade.php -->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="{!! csrf_token() !!}"/>
        <meta name="csrf-token" content="{!! csrf_token() !!}"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height" />

        <meta name="keywords" content="{{ Setting::get('meta_keywords') }}">
        <meta name="description" content="{{ Setting::get('meta_description') }}">
        <link rel="icon" type="image/x-icon" href="{{asset('images/favicon.ico')}}">

        <meta content="{{ url('/') }}" property="og:url" />
        <meta content="{!! url('setting/'.Setting::get('logo')) !!}" property="og:image" />
        <meta content="{{ Setting::get('meta_description') }}" property="og:description" />
        <meta content="{{ Setting::get('site_name') }}" property="og:title" />
        <meta content="website" property="og:type" />
        <meta content="{{ Setting::get('site_name') }}" property="og:site_name" />


        <title>{{ Setting::get('site_title') }}</title>

        <link href="{{ asset('/themes/default/assets/css/flag-icon.css') }}" rel="stylesheet">
        <link href="{{ asset('/themes/default/assets/css/custom.css') }}" rel="stylesheet">
        <link href="{{ url('css/extra.css') }}" rel="stylesheet">
        <!--videojs-->
        <link href="//vjs.zencdn.net/7.8.2/video-js.min.css" rel="stylesheet">
        {{--
        <link href="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" rel="stylesheet">
        --}}

        {{-- {!! Theme::asset()->styles() !!} --}}
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

        <!-- %PSG: inject Theme script assets... (BEGIN) -->
        {{-- Theme::asset()->scripts() | %PSG: this is loading main.css as a script (!), load main.js direct below --}}
        <script src="{{ asset('/themes/default/assets/js/main.js') }}"></script>
        <!-- %PSG: inject Theme script assets... (END) -->

        @if(Setting::get('google_analytics') != NULL)
            {!! Setting::get('google_analytics') !!}
        @endif
        <script src="{{ asset('/themes/default/assets/js/lightgallery.js') }}"></script>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
        <script src="{{ asset('js/app.onlineMonitor.js') }}"></script>
    </head>
    <body @if(Setting::get('enable_rtl') == 'on') class="direction-rtl" @endif>
        {!! Theme::partial('header') !!}

        <div class="main-content">
            {!! Theme::content() !!}
        </div>

        {!! Theme::partial('right-sidebar') !!}

        {!! Theme::partial('footer') !!}
{{--        {{  Request::path()}}--}}

          <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>

        <script>
          @if(Config::get('app.debug'))
            // Pusher.logToConsole = true;
          @endif
            var pusherConfig = {
                token: "{{ csrf_token() }}",
                PUSHER_KEY: "{{ config('broadcasting.connections.pusher.key') }}",
                CLUSTER:  "{{ config('broadcasting.connections.pusher.options.cluster') }}"
            };
          let pusherKey = '{{ config('broadcasting.connections.pusher.key') }}';
          let pusherCluster = '{{ config('broadcasting.connections.pusher.options.cluster') }}';
       </script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.5.0/socket.io.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
        <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
        <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
        <script>
            let setLastSeenURL = "{{ route('update-user-status') }}";
            $('.search-btn').on('click', function () {
                $('.mobile-search').toggle();
            });
        </script>
        {!! Theme::asset()->container('footer')->scripts() !!}

        <div id="global-modal-placeholder" class="modal fade" tabindex="1" role="dialog"></div> <!-- MODAL -->

{{--
--}}
<script type="application/javascript">

const g_php2jsVars = <?php echo json_encode($g_php2jsVars ?? []); ?>;

$(document).ready(function () {

  // Site-wide generic handler for opening a modal
  $(document).on('click', '.tag-clickme_to_open_modal.tag-global_modal', function (e) {
    e.preventDefault();
    var context = $(this);
    var url = context.attr('href');
    var payload = {};
    $.getJSON(url, payload, function(response) {
      $('#global-modal-placeholder').html(response.html);
      $('#global-modal-placeholder').modal('toggle');
      /*
      if ( response.hasOwnProperty('cb_func') ) {
      }
      */
    });
    return false;
  });

  //$('body .follow-user').click(); // DEBUG

});
</script>

<script src="{{ asset('themes/default/assets/js/currency.min.js') }}"></script>
<script src="{{ asset('js/app/tippingUtils.js') }}"></script>


    </body>   

</html>