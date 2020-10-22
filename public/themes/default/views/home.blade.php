<style>
    .timeline-posts {
        margin-top: 50px;
    }
    .create-post-form .change-layout {
        display: inline-block;
        float: right;
        width: 30px;
        margin-right: 5px;
    }
    .create-post-form .change-layout img {
        width: 100%;
    }
    .change-layout.one-column {
        display: none;
    }
    .create-post-form .change-layout i {
        font-size: 25px;
    }
    @media screen and (min-width: 1200px) {
        .timeline-condensed-column .panel-body, .jscroll-added .panel-body, .timeline-condensed-column > .col-lg-4 .panel-body {
            height: 200px;
            overflow: auto;
        }
    }
    .timeline-condensed-column .panel-heading .user-post-details li:last-child, .jscroll-added .panel-heading .user-post-details li:last-child, .timeline-condensed-column > .col-lg-4 .panel-heading .user-post-details li:last-child {
        max-width: 100px;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }
    
    .panel-post .single-image-panel {
        padding: 0;
    }
    .single-image-panel .single-image {
        margin: 0 !important;
    }
    
    .single-image-panel img{
        margin: 0 !important;
    }

    .single-image-panel a {
        margin: 0!important;
    }
</style>
		<div class="container">
			<div class="row">
                <div class="col-md-7 col-lg-8">
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
					@if($mode != "eventlist")
						{!! Theme::partial('create-post',compact('timeline','user_post')) !!}
						<div class="timeline-posts timeline-default">
							@if($posts->count() > 0)
								@foreach($posts as $post)
                                    @if($post->type == \App\Post::PAID_TYPE)
                                        @if($post->user->activeSubscribers->contains(Auth::user()->id) || $post->user->id == Auth::user()->id)
									    {!! Theme::partial('post',compact('post','timeline','next_page_url')) !!}
                                        @endif
                                    @else
                                        @if(canUserSeePost(Auth::id(), $post->user->id) || Auth::user()->PurchasedPostsArr->contains($post->id))
                                        {!! Theme::partial('post',compact('post','timeline','next_page_url')) !!}
                                        @endif
                                    @endif
								@endforeach
							@else
								<div class="no-posts alert alert-warning">{{ trans('common.no_posts') }}</div>
							@endif
						</div>
						<div class="timeline-posts timeline-condensed-column row" style="display: none">
							@if($posts->count() > 0)
                                @foreach($posts as $post)                                    
                                    @if($post->type == \App\Post::PAID_TYPE)
                                        @if($post->user->activeSubscribers->contains(Auth::user()->id)  || $post->user->id == Auth::user()->id)
                                            {!! Theme::partial('post_condensed_column',compact('post','timeline','next_page_url')) !!}
                                        @endif
                                    @else
                                        @if(canUserSeePost(Auth::id(), $post->user->id) || Auth::user()->PurchasedPostsArr->contains($post->id))
                                        {!! Theme::partial('post_condensed_column',compact('post','timeline','next_page_url')) !!}
                                        @endif
                                    @endif
								@endforeach
                            @else
								<div class="no-posts alert alert-warning">{{ trans('common.no_posts') }}</div>
							@endif
						</div>
					@else
						{!! Theme::partial('eventslist',compact('user_events','username')) !!}
					@endif
				</div>

				<div class="col-md-5 col-lg-4">
					{!! Theme::partial('home-rightbar',compact('suggested_users', 'suggested_groups', 'suggested_pages', 'totalTip', 'subscriptionAmount')) !!}
				</div>
			</div>
		</div>

<script type="text/javascript">
    $('.change-layout').click(function () {
        $('.timeline-default, .timeline-condensed-column').toggle();
        condensedLayout = !condensedLayout;
        $.each($(".timeline-condensed-column>.panel"), function (i) {
            $(this).wrap('<div class="col-lg-4"></div>');
        });
        $('.three-column, .one-column').toggle();
        if (!condensedLayout) {
            $.each($(".timeline-default>.col-lg-4 .panel"), function (i) {
                $(this).unwrap();
            });
        }
    });
    {{--var module = { };--}}
    {{--let pusherKey = '{{ config('broadcasting.connections.pusher.key') }}';--}}
    {{--let pusherCluster = '{{ config('broadcasting.connections.pusher.options.cluster') }}';--}}
</script>
{{--{!! Theme::asset()->container('footer')->usePath()->add('echo', 'js/echo.iife.js') !!}--}}
{{--{!! Theme::asset()->container('footer')->usePath()->add('pusher', 'js/pusher.js') !!}--}}
