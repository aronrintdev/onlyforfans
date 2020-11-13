<style>
    .grid {
        min-height: 70vh;
    }

    .grid-item a img {
        height: 100% !important;
        width: 100% !important;
        object-fit: cover;
    }

    .grid-item a {
        margin-bottom: 0;
    }

    .grid-sizer,
    .grid-item {
        width: 32%;
        float: left;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .jscroll-inner {
        overflow: hidden !important;
    }

    .post-modal .modal-header {
        display: flex;
        align-items: center;
    }

    .post-modal .modal-header .modal-title {
        margin-left: 15px;
    }

    .post-modal .modal-header .post-modal-avatar {
        height: 50px;
        width: 50px;
        border-radius: 50%;
    }

    .post-modal .modal-body {
        padding: 0;
    }

    .post-modal .modal-body img {
        width: 100%;
        height: auto;
    }

    .page-load-status {
        display: none; /* hidden by default */
        padding-top: 20px;
        border-top: 1px solid #DDD;
        text-align: center;
        color: #777;
    }

    .grid.are-images-unloaded {
        opacity: 0;
    }

    .loader-ellips {
        font-size: 20px; /* change size here */
        position: relative;
        width: 4em;
        height: 1em;
        margin: 10px auto;
    }

    .loader-ellips__dot {
        display: block;
        width: 1em;
        height: 1em;
        border-radius: 0.5em;
        background: #555; /* change color here */
        position: absolute;
        animation-duration: 0.4s;
        animation-timing-function: ease;
        animation-iteration-count: infinite;
    }

    .loader-ellips__dot:nth-child(1),
    .loader-ellips__dot:nth-child(2) {
        left: 0;
    }
    .loader-ellips__dot:nth-child(3) { left: 1.5em; }
    .loader-ellips__dot:nth-child(4) { left: 3em; }

    @keyframes reveal {
        from { transform: scale(0.001); }
        to { transform: scale(1); }
    }

    @keyframes slide {
        to { transform: translateX(1.5em) }
    }

    .loader-ellips__dot:nth-child(1) {
        animation-name: reveal;
    }

    .loader-ellips__dot:nth-child(2),
    .loader-ellips__dot:nth-child(3) {
        animation-name: slide;
    }

    .loader-ellips__dot:nth-child(4) {
        animation-name: reveal;
        animation-direction: reverse;
    }

    .grid-item #unmoved-fixture, .grid-item #unmoved-fixture video {
        height: 100%;
        object-fit: cover;
    }

    .grid-item.multiple-images a {
        min-height: 180px;
        width: 100% !important;
    }

    .grid-item.multiple-images:before, .video-item:before {
        content: "\f24d ";
        font: normal normal normal 18px/1 FontAwesome;
        position: absolute;
        top: 15px;
        right: 25px;
        color: #232222;
    }

    .video-item:before {
        content: "\f04b";
        z-index: 99;
    }

    .post-detail-modal .modal-body {
        padding: 0;
    }

    .post-detail-modal .modal-body .panel {
        margin-bottom: 0;
        box-shadow: none;
    }

    .post-detail-modal .modal-body .panel .panel-body {
        padding: 0;
        padding-bottom: 8px;
    }

    .locked-content {
        font-size: 80px;
        color: #fff;
        text-align: center;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 250px;
        width: 100%;
        max-width: 100%;
    }

    .locked-content .locked-content-wrapper {
        display: flex;
        flex-direction: column;
    }

    .more-height {
        min-height: 80vh !important;
    }


    .post-image-holder.multiple-images a {
        width: 100%;
    }
    
    @media screen and (max-width: 768px) {
        .grid-sizer,
        .grid-item {
            width: 31.5%;
        }
    }
    
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .default-post.panel-post .image-with-blur .single-image {
        height: 440px;
        position: relative;
    }

    @media screen and (max-width: 576px) {
        .default-post.panel-post .image-with-blur .single-image {
            height: 250px
        }
    }

    .default-post.panel-post .image-with-blur .single-image a img {
        height: auto;
        width: auto;
        max-height: 100% !important;
        max-width: 100% !important;
    }

    .default-post.panel-post .image-with-blur .single-image a {
        position: absolute;
        text-align: center;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .default-post.panel-post .image-with-blur .single-image .first-image {
        z-index: 9 !important;
    }

    .default-post.panel-post .with-text {
        display: flex;
        max-height: inherit !important;
    }

    .default-post.panel-post .with-text a {
        flex-grow: 1;
        height: 100%;
        max-height: 100%;
    }

    .default-post.panel-post .with-text img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        max-height: 100% !important;
    }

    @media screen and (max-width: 576px) {
        .default-post.panel-post .with-text img {
            height: 250px
        }
    }
</style>
<!-- main-section -->
	<!-- <div class="main-content"> -->
		<div class="container purchased-post-container">
			<div class="row">
                <div class="col-md-12 col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading no-bg panel-settings" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                            <h3 class="panel-title">
                                {{ trans('common.purchased_items') }}
                            </h3>
                        </div>
                        <div class="panel-body nopadding">
                            <ul class="nav nav-pills heading-list">
                                <li class="active"><a href="#posts" data-toggle="pill" class="text">{{ trans('common.posts') }}<span></span></a></li>
                                <!-- <li class="divider">&nbsp;</li> -->
                            </ul>
                        </div>
                        <div class="tab-content" style="margin-top:15px;">
                            <div id="posts" class="tab-pane fade active in">
{{--                                <div style="display: flex; justify-content: space-between">--}}
{{--                                    <div class="input-group explore-search-bar"--}}
{{--                                         style="display: none; margin-bottom: 10px; width: 100%;">--}}
{{--                        <span class="input-group-btn">--}}
{{--                            <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>--}}
{{--                        </span>--}}
{{--                                        <input type="text" id="explorePosts" class="form-control"--}}
{{--                                               placeholder="{{ trans('messages.search_post_placeholder') }}">--}}
{{--                                    </div><!-- /input-group -->--}}
{{--                                </div>--}}
                                @if (Session::has('message'))
                                    <div class="alert alert-{{ Session::get('status') }}" role="alert">
                                        {!! Session::get('message') !!}
                                    </div>
                                @endif

                                @if(isset($active_announcement))
                                    <div class="announcement alert alert-info">
                                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                        <h3>{{ $active_announcement->title }}</h3>
                                        <p>{{ $active_announcement->description }}</p>
                                    </div>
                                @endif
                                <div class="no-posts alert alert-warning" style="display: none">{{ trans('common.no_posts') }}</div>
                                <div class="timeline-posts timeline-default">
                                    @if(count($posts) > 0)
                                        @foreach($posts as $post)
                                            {!! Theme::partial('post',compact('post','timeline','next_page_url')) !!}
                                        @endforeach
                                    @else
                                        <p class="no-posts">{{ trans('messages.no_posts') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
		</div>
	<!-- </div> -->
<!-- /main-section -->
<script src="https://unpkg.com/infinite-scroll@3/dist/infinite-scroll.pkgd.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
<script src='https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.js'></script>
<script type="text/javascript">
    let $grid;
    window.onload = function () {
        $('.explore-search-bar, .purchased-posts-title').fadeIn();
        
        const ajaxCall = function () {
            let search = $('#explorePosts').val();
            $.ajax({
                url: "{{ route('purchased-post.search') }}",
                type: 'get',
                dataType: 'json',
                data: { 'search': search },
                success: function (result) {
                    let $ajaxGrid;
                    $('.no-posts').hide();
                    $('.timeline-posts').removeClass('lesser-hight');
                    if (result.data == '') {
                        $('.no-posts').show();
                        $('.timeline-posts').addClass('lesser-hight');
                    }
                    $('.timeline-posts').html(result.data);
                },
                error: function (result) {
                    console.log(result);
                }
            });
        }

        function debounce(cb, interval, immediate) {
            let timeout;

            return function() {
                let context = this, args = arguments;
                let later = function() {
                    timeout = null;
                    if (!immediate) cb.apply(context, args);
                };

                let callNow = immediate && !timeout;

                clearTimeout(timeout);
                timeout = setTimeout(later, interval);

                if (callNow) cb.apply(context, args);
            };
        };
        
        // document.getElementById('explorePosts').onkeypress = debounce(ajaxCall, 1500);
        // document.getElementById('explorePosts').onkeydown = debounce(ajaxCall, 1500);
    };
</script>
