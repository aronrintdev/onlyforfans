<style>    
    .grid-item, .grid-item a {
        height: 180px !important;
        margin-bottom: 30px;        
    }

    .grid-item a img {
        height: 100% !important;
        width: 100% !important;
        object-fit: cover;
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
        height: 100%;
    }

    .locked-content .locked-content-wrapper {
        display: flex;
        flex-direction: column;
    }
    
    @media screen and (max-width: 992px) {
        .grid-item {
            height: 250px !important;
        }

        .grid-item a {
            min-height: 250px !important;
        }
    }
</style>
<!-- main-section -->
	<!-- <div class="main-content"> -->
		<div class="container">
			<div class="row">
                <div class="col-md-7 col-lg-8">

                    <div style="display: flex; margin-bottom: 10px; justify-content: space-between">
                        <div class="input-group explore-search-bar" style="display: none; margin-bottom: 0; margin-right: 10px; width: calc(100% - 150px)">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                            </span>
                            <input type="text" id="explorePosts" class="form-control" placeholder="{{ trans('messages.search_placeholder') }}">
                        </div><!-- /input-group -->
                        <a style="display: none" href="{{ route('purchased-posts') }}" class="btn btn-success purchased-posts">Show Purchased</a>
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

                    <div class="explore-posts">
                        @if($posts->count() > 0)
                            <div class="row">
                                @foreach($posts as $post)
                                    @if($post->type != \App\Post::PAID_TYPE)
                                    {!! Theme::partial('explore_posts',compact('post','timeline','next_page_url')) !!}
                                    @endif
                                @endforeach
                            </div>
                            <div class="no-posts alert alert-warning" style="display: none">{{ trans('common.no_posts') }}</div>
                        @endif
                    </div>
				</div><!-- /col-md-6 -->

				<div class="col-md-5 col-lg-4">
					{!! Theme::partial('home-rightbar',compact('suggested_users', 'suggested_groups', 'suggested_pages')) !!}
				</div>
			</div>
		</div>
	<!-- </div> -->
<!-- /main-section -->

<script type="text/javascript">
    window.onload = function () {
        $('.explore-search-bar, .purchased-posts').fadeIn();
        
        $('.explore-posts .row').jscroll({
            // loadingHtml: '<img src="loading.gif" alt="Loading" /> Loading...',
            nextSelector: 'a.jscroll-next:last',
            callback : function()
            {
                emojify.run();
                $('.post-description').linkify()
                jQuery("time.timeago").timeago();
            }
        });

        const ajaxCall = function () {
            let search = $('#explorePosts').val();
            $.ajax({
                url: "{{ route('post.search') }}",
                type: 'get',
                dataType: 'json',
                data: { 'search': search },
                success: function (result) {
                    $('.explore-posts .no-posts').hide();
                    if (result.data == '') {
                        $('.explore-posts .no-posts').show();
                    }
                    $('.explore-posts .jscroll-inner').html(result.data);
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
    };
</script>
