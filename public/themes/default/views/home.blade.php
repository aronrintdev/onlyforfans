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
		
		.timeline-condensed-column .shared-post {
			height: 179px;
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
	.single-image-panel .actions-count {
		padding: 7px 15px;
	}
    
    .single-image-panel img{
        margin: 0 !important;
    }

    .single-image-panel a {
        margin: 0!important;
    }
    
    .post-audio-holder audio {
        outline: none;
        width: 100%;
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
		width: 250px;
		margin: auto;
		text-align: center;
	}

	.subscriberFilterModal .panel-body ul li p{
		font-weight: bold;
	}

	@media (min-width: 768px) {
		.subscriberFilterModal .modal-dialog {
			width: 450px;
		}
	}
	
	input[type="number"] {
		appearance: none;
		-moz-appearance: textfield !important;
	}
</style>
		<div class="container">
			<div class="row">
                <div class="col-md-7 col-lg-8 content-wrapper">
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

				<div class="col-md-5 col-lg-4 sidebar-wrapper">
					{!! Theme::partial('home-rightbar',compact('suggested_users', 'suggested_groups', 'suggested_pages', 'totalTip', 'subscriptionAmount')) !!}
				</div>
			</div>
		</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
{!! Theme::asset()->container('footer')->usePath()->add('currency', 'js/currency.min.js') !!}
<script type="text/javascript">
    $('.change-layout').click(function () {
        $('.timeline-default, .timeline-condensed-column, .sidebar-wrapper').toggle();
        $('.content-wrapper').toggleClass('col-lg-12 col-lg-8');
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
{{--{!! Theme::asset()->container('footer')->usePath()->add('echo', 'js/echo.iife.js') !!}--}}
{{--{!! Theme::asset()->container('footer')->usePath()->add('pusher', 'js/pusher.js') !!}--}}
