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
</style>
<!-- main-section -->
	<!-- <div class="main-content"> -->
		<div class="container">
			<div class="row">
                <div class="col-lg-12">

                    <div style="display: flex; justify-content: space-between">
                        <div class="input-group explore-search-bar" style="display: none; margin-bottom: 10px; width: calc(100% - 200px)">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                        </span>
                            <input type="text" id="explorePosts" class="form-control" placeholder="{{ trans('messages.search_post_placeholder') }}">
                        </div><!-- /input-group -->
                        <h2 style="margin: 0; display: none" class="purchased-posts-title">Purchased Posts</h2>
                    </div>                        
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
                    <div class="explore-posts grid are-images-unloaded">
                        @if($posts->count() > 0)
                            <div class="grid-sizer"></div>
                            @foreach($posts as $post)
                                @if($post->type != \App\Post::PAID_TYPE)
                                {!! Theme::partial('explore_posts',compact('post','timeline','next_page_url')) !!}
                                @endif
                            @endforeach
                        @else
                            <div class="no-posts alert alert-warning">{{ trans('common.no_posts') }}</div>
                        @endif
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
                    $('.explore-posts').removeClass('lesser-hight');
                    if (result.data == '') {
                        $('.no-posts').show();
                        $('.explore-posts').addClass('lesser-hight');
                    }
                    $('.grid').addClass('are-images-unloaded');
                    $('.explore-posts').html(result.data);
                    $('.explore-posts').prepend('<div class="grid-sizer"></div>');
                    $ajaxGrid = $('.grid').masonry({
                        // options
                        itemSelector: '.none',
                        percentPosition: true,
                        columnWidth: '.grid-sizer',
                        gutter: 10
                    });

                    var msnry = $ajaxGrid.data('masonry');

                    // initial items reveal
                    $ajaxGrid.imagesLoaded( function() {
                        $ajaxGrid.removeClass('are-images-unloaded');
                        $ajaxGrid.masonry( 'option', { itemSelector: '.grid-item' });
                        var $items = $ajaxGrid.find('.grid-item');
                        $ajaxGrid.masonry( 'appended', $items );
                    });

                    $('.explore-posts').infiniteScroll({
                        // options
                        path: '.next-link',
                        append: '.grid-item',
                        outlayer: msnry,
                        history: false
                    });
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
        
        document.getElementById('explorePosts').onkeypress = debounce(ajaxCall, 1500);
        document.getElementById('explorePosts').onkeydown = debounce(ajaxCall, 1500);
        
        $grid = $('.grid').masonry({
            // options
            itemSelector: '.none',
            percentPosition: true,
            columnWidth: '.grid-sizer',
            gutter: 10,
            visibleStyle: { transform: 'translateY(0) scale(1)', opacity: 1 },
            hiddenStyle: { transform: 'translateY(100px) scale(.8)', opacity: 0 },
        });

        var msnry = $grid.data('masonry');

        // initial items reveal
        $grid.imagesLoaded( function() {
            $grid.removeClass('are-images-unloaded');
            $grid.masonry( 'option', { itemSelector: '.grid-item' });
            var $items = $grid.find('.grid-item');
            $grid.masonry( 'appended', $items );
        });

        $('.explore-posts').infiniteScroll({
            // options
            path: '.next-link',
            append: '.grid-item',
            outlayer: msnry,
            history: false
        });

        function gridWrapperHeight()
        {
            if (screen.height > 1200) {
                $('.grid').addClass('more-height')
            } else {
                $('.grid').removeClass('more-height')
            }
        }

        gridWrapperHeight();

        $( window ).resize(function () {
            gridWrapperHeight();
        })
    };
</script>
