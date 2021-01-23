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
    }

    @media screen and (max-width: 768px) {
        .grid-sizer,
        .grid-item {
            width: 31% !important;
        }
        
        .close-post-modal {
            height: 20px;
            display: block !important;
            width: 20px;
            position: absolute;
            top: -5px;
            right: -5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.5);
            background: #fff !important;
            opacity: 1;
            border-radius: 50%;
        }
    }
    
    .grid-item {
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
        /*display: none; !* hidden by default *!*/
        padding-top: 20px;
        border-top: 1px solid #DDD;
        text-align: center;
        color: #777;
    }
    
    .page-load-status p {
        display: none;
        display: none;
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
    
    .grid-item .post-image-holder.multiple-images a {
        min-height: 180px;
        max-height: inherit;
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

    .lesser-hight {
        min-height: 30vh;
    }
    
    @media screen and (max-width: 768px) {
        .grid-sizer,
        .grid-item {
            width: 31.5%;
        }
    }

    .post-image-holder.multiple-images a {
        width: 100%;
    }
    
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }


    .total-spent, .total-tipped, .subscribed-over, .inactive-over {
        position: relative;
    }

    .filter-input {
        border: 0;
        outline: none;
        text-align: center;
        font-weight: bold;
        color: #fff;
    }

    .subscriberFilterModal .panel-footer {
        text-align: right;
    }

    .subscriberFilterModal .panel-heading {
        border-bottom: none !important;
    }
    
    .subscriberFilterModal .panel-heading .post-author {
        padding: 0 20px;
    }
    
    .subscriberFilterModal .panel-heading .user-post-details ul {
        padding-right: 0 !important;
    }
    
    .subscriberFilterModal .panel-body ul li span {
        color: #298ad3;
        position: absolute;
        height: 25px;
        border-radius: 50%;
        width: 25px;
        /*top: 0;*/
        vertical-align: middle;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        transition: .3s;
    }

    .subscriberFilterModal .text-wrapper {
        top: 0;
        left: 50%;
        position: absolute;
        transform: translateX(-50%);
        bottom: 0;
        display: block;
        max-width: 100%;
        width: 80%;
    }

    .subscriberFilterModal ul li span:hover {
        background: #e7f3ff;
    }

    .subscriberFilterModal ul li span:active {
        background: #9dd5ff;
    }

    .subscriberFilterModal ul li span.decrement {
        left: 0;
    }

    .subscriberFilterModal ul li span.increment {
        right: 0;
    }

    .subscriberFilterModal ul {
        list-style: none;
        padding: 0 25px;
    }

    .subscriberFilterModal .panel-body ul li {
        width: 180px;
        margin: auto;
        text-align: center;
    }

    .subscriberFilterModal .panel-body ul li p{
        font-weight: bold;
    }

    .subscriberFilterModal .modal-dialog-centered {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 93%;
    }
    
    .subscriberFilterModal .modal-content {
        width: 270px;
    }

    input[type="number"] {
        appearance: none;
        -moz-appearance: textfield !important;
    }

    .explore-post .single-image {
        max-height: inherit !important;
        height: 400px;
        overflow: hidden;
    }

    .explore-post.panel-post .image-with-blur .single-image .first-image {
        z-index: 9;
    }

    .explore-post.panel-post .image-with-blur .single-image {
        height: 440px;
        position: relative;
    }

    .explore-post.panel-post .image-with-blur .single-image a {
        position: absolute;
        text-align: center;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .explore-post.panel-post .image-with-blur .single-image img {
        height: auto !important;
        width: auto !important;
        max-height: 100% !important;
        max-width: 100% !important;
    }
</style>
<!-- main-section -->
	<!-- <div class="main-content"> -->
		<div class="container">
			<div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12">

{{--                    <div style="display: flex; margin-bottom: 10px; justify-content: space-between">--}}
{{--                        <div class="input-group explore-search-bar" style="display: none; margin-bottom: 0; margin-right: 10px; width: 100%">--}}
{{--                            <span class="input-group-btn">--}}
{{--                                <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>--}}
{{--                            </span>--}}
{{--                            <input type="text" id="explorePosts" class="form-control" placeholder="{{ trans('messages.search_post_placeholder') }}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
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
                                    @if($post->type != \App\Post::PRICE_TYPE)
                                    @if(canUserSeePost(Auth::id(), $post->user->id))
                                        {!! Theme::partial('explore_posts',compact('post','timeline','next_page_url')) !!}
                                    @else
                                        @if(isset($next_page_url))
                                            <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
                                        @endif
                                    @endif
                                @endif
                                @endforeach
                        @endif
                    </div>
				</div><!-- /col-md-6 -->

				<!-- <div class="col-md-5 col-lg-4">
				{{--	{!! Theme::partial('home-rightbar',compact('suggested_users', 'suggested_groups', 'suggested_pages')) !!}  --}}
				</div> -->
			</div>
		</div>
	<!-- </div> -->
<!-- /main-section -->

<script src="https://unpkg.com/infinite-scroll@3/dist/infinite-scroll.pkgd.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
{{--<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.js"></script>--}}
<script src='https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.js'></script>
<script src="{{ asset('themes/default/assets/js/currency.min.js') }}"></script>
<script type="text/javascript">
    let nextUrl = $('a.jscroll-next:last-child').attr('href');
    let $grid;
    window.onload = function () {
        
        $('.explore-search-bar, .purchased-posts').fadeIn();

        
        // $('.explore-posts').jscroll({
        //     // loadingHtml: '<img src="loading.gif" alt="Loading" /> Loading...',
        //     nextSelector: 'a.jscroll-next:last',
        //     callback : function()
        //     {
        //         emojify.run();
        //         $('.post-description').linkify()
        //         jQuery("time.timeago").timeago();
        //     }
        // });

        const ajaxCall = function () {
            let search = $('#explorePosts').val();
            $.ajax({
                url: "{{ route('post.search') }}",
                type: 'get',
                dataType: 'json',
                data: { 'search': search },
                success: function (result) {
                    $('.filter-input').trigger('change');
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

                    $iScroll = $('.explore-posts').infiniteScroll({
                        // options
                        path: '.next-link',
                        append: '.grid-item',
                        outlayer: msnry,
                        history: false,
                        prefill: true,
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
        
        // document.getElementById('explorePosts').onkeypress = debounce(ajaxCall, 1500);
        // document.getElementById('explorePosts').onkeydown = debounce(ajaxCall, 1500);

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

        $grid.on('append.infiniteScroll', function () {
            setTimeout(function () {
                $grid.masonry();
            }, 500)
        })
        $grid.on('request.infiniteScroll', function () {
            $grid.masonry();
        })
        $grid.on('load.infiniteScroll', function () {
            setTimeout(function () {
                $grid.masonry();
            }, 500)
        })
        
        // initial items reveal
        $grid.imagesLoaded( function() {
            $grid.removeClass('are-images-unloaded');
            $grid.masonry( 'option', { itemSelector: '.grid-item' });
            var $items = $grid.find('.grid-item');
            $grid.masonry( 'appended', $items );
        });
                
        $iScroll = $('.explore-posts').infiniteScroll({
            // options
            path: '.next-link',
            append: '.grid-item',
            outlayer: msnry,
            history: false,
            prefill: true,
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

    $(document).on('click', 'span.decrement', function () {
        let input = $(this).siblings('input.filter-input');
        let val = input.val() != '' ? parseFloat(input.val()) : 0;

        if (val != NaN) {
            if (input.data('id') == 1) {
                input.val((val - 100) < 0 ? 0 : (val - 100)).trigger('keyup');
            } else if(input.data('id') == 2) {
                input.val((val - 10) < 0 ? 0 : (val - 10)).trigger('keyup');
            } else {
                input.val((val - 1) < 0 ? 0 : (val - 1)).trigger('keyup');
            }

        }
    });

    $(document).on('click', 'span.increment', function () {
        let input = $(this).siblings('input.filter-input');
        let val = input.val() != '' ? parseFloat(input.val()) : 0;
        if (val != NaN) {
            if (input.data('id') == 1) {
                input.val(val + 100).trigger('keyup');
            } else if(input.data('id') == 2) {
                input.val(val + 10).trigger('keyup');
            } else if(input.data('id') == 3) {
                input.val((val + 1) > 12 ? val : (val + 1)).trigger('keyup');
            } else if(input.data('id') == 4) {
                input.val((val + 1) > 30 ? val : (val + 1)).trigger('keyup');
            }
        }
    });

    $('.subscriberFilterModal').on('show.bs.modal', function () {
        $('.filter-input').trigger('keyup');
    });

    $('.subscriberFilterModal').on('hidden.bs.modal', function () {
        $(this).find('.text-wrapper').text('');
        $(this).find('.etTipAmount').val('');
        $(this).find('#tipNote').val('');
    });

    $(document).on('keyup', '.filter-input', function () {
        let activeFilter = $(this).closest('li').find('input[type="radio"]');
        activeFilter.prop('checked', true);
        let val = $(this).val() != '' ? parseFloat($(this).val()) : 0;
        if($(this).data('id') == 1) {
            $(this).next('.text-wrapper').text($(this).val() + ' USD');
        }else if($(this).data('id') == 2) {
            $(this).val(val > 200 ? val : val);
            $(this).next('.text-wrapper').text(currency($(this).val()).format() + ' USD');
        }else if($(this).data('id') == 3) {
            $(this).val(val > 12 ? 12 : val);
            $(this).next('.text-wrapper').text($(this).val() + ' Month');
        } else if($(this).data('id') == 4) {
            $(this).val(val > 30 ? 30 : val);
            $(this).next('.text-wrapper').text($(this).val() + ' Day');
        }
    });
</script>
