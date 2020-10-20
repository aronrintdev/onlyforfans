<style>    
    .change-layout {
        display: none;
    }
    
    .switch-layout img {
        width: 25px;
    }

    .switch-wrapper {
        vertical-align: top;
        margin-right: 5px;
    }

	.switch-wrapper i {
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
</style>
<div class="container profile-posts">
	<div class="row">
		<div class="col-md-12">
			{!! Theme::partial('user-header',compact('timeline', 'liked_post', 'liked_pages','user','joined_groups','followRequests','following_count',
			'followers_count','follow_confirm','user_post','joined_groups_count','guest_events', 'user_lists')) !!}
			<div class="row">
				<div class=" timeline">
					<div class="col-md-4">
						{!! Theme::partial('user-leftbar',compact('timeline','user','follow_user_status','own_groups','own_pages','user_events')) !!}
					</div>
					<div class="col-md-8">
						@if($timeline->type == "user" && ($user->id == Auth::user()->id || canCreatePost(Auth::user(), $user)))
							{!! Theme::partial('create-post',compact('timeline','user_post')) !!}
						@endif
						<div class="lists-dropdown-menu">
								<ul class="list-inline text-right no-margin">
									<li class="dropdown">
										<a href="#" class="dropdown-togle lists-dropdown-icon" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="color: #298ad3; display: inline-flex">
                                            <svg data-toggle="tooltip" title="Sort" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                              <path fill-rule="evenodd" d="M7 11.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z"/>
                                            </svg>
										</a>
										<ul class="post-dropdown-menu dropdown-menu profile-dropdown-menu-content">
											<li class="main-link">
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="period-post" id="periodAllTime" value="all" {{$period == 'all' ? "checked" : ""}}>
													<label class="red-list-label" for="periodAllTime">
														All time
													</label>
												</div>
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="period-post" id="periodLastThreeM" value="3m" {{$period == '3m' ? "checked" : ""}}>
													<label class="red-list-label" for="periodLastThreeM">
														Last three months
													</label>
												</div>
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="period-post" id="periodLastOneM" value="1m" {{$period == '1m' ? "checked" : ""}}>
													<label class="red-list-label" for="periodLastOneM">
														Last month
													</label>
												</div>
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="period-post" id="periodLastW" value="1w" {{$period == '1w' ? "checked" : ""}}>
													<label class="red-list-label" for="periodLastW">
														Last week
													</label>
												</div>
											</li>
											<hr>
											<li class="main-link">
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="sort-profile-post" id="sortByLatest" value="latest" {{$sort_by == 'latest' ? "checked" : ""}}>
													<label class="red-list-label" for="sortByLatest">
														Latest Posts
													</label>
												</div>
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="sort-profile-post" id="soryByLiked" value="liked" {{$sort_by == 'liked' ? "checked" : ""}}>
													<label class="red-list-label" for="soryByLiked">
														Most Liked
													</label>
												</div>
											</li>
											<hr>
											<li class="main-link">
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="order-profile-post" id="orderByASC" value="asc" {{$order_by == 'asc' ? "checked" : ""}}>
													<label class="red-list-label" for="orderByASC">
														Ascending
													</label>
												</div>
												<div class="form-check">
													<input class="red-checkbox" type="radio" name="order-profile-post" id="orderByDESC" value="desc" {{$order_by == 'desc' ? "checked" : ""}}>
													<label class="red-list-label" for="orderByDESC">
														Descending
													</label>
												</div>
											</li>
										</ul>
									</li>
                                    <li class="switch-wrapper">
                                        <a href="javascript:;" class="switch-layout three-column">
											<svg data-toggle="tooltip" title="Expand layout" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrows-angle-expand" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M5.828 10.172a.5.5 0 0 0-.707 0l-4.096 4.096V11.5a.5.5 0 0 0-1 0v3.975a.5.5 0 0 0 .5.5H4.5a.5.5 0 0 0 0-1H1.732l4.096-4.096a.5.5 0 0 0 0-.707zm4.344-4.344a.5.5 0 0 0 .707 0l4.096-4.096V4.5a.5.5 0 1 0 1 0V.525a.5.5 0 0 0-.5-.5H11.5a.5.5 0 0 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 0 .707z"/>
                                            </svg>
										</a>
                                    </li>
                                    <li class="switch-wrapper" style="display: none">
                                        <a href="javascript:;" class="switch-layout one-column">
											<svg data-toggle="tooltip" title="Contract layout" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrows-angle-contract" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M.172 15.828a.5.5 0 0 0 .707 0l4.096-4.096V14.5a.5.5 0 1 0 1 0v-3.975a.5.5 0 0 0-.5-.5H1.5a.5.5 0 0 0 0 1h2.768L.172 15.121a.5.5 0 0 0 0 .707zM15.828.172a.5.5 0 0 0-.707 0l-4.096 4.096V1.5a.5.5 0 1 0-1 0v3.975a.5.5 0 0 0 .5.5H14.5a.5.5 0 0 0 0-1h-2.768L15.828.879a.5.5 0 0 0 0-.707z"/>
                                            </svg>
										</a>
                                    </li>                                    
								</ul>
							</div>
						<div class="timeline-posts timeline-default">
							@if(count($posts) > 0)
								@foreach($posts as $post)
                                    @if($post->type == \App\Post::PAID_TYPE)
                                        @if($post->user->activeSubscribers->contains(Auth::user()->id)  || $post->user->id == Auth::user()->id)
                                            {!! Theme::partial('post',compact('post','timeline','next_page_url', 'user')) !!}
                                        @endif
                                    @else
                                        @if(canUserSeePost(Auth::id(), $post->user->id, $user->timeline->id) || $post->type == \App\Post::PRICE_TYPE)
                                        {!! Theme::partial('post',compact('post','timeline','next_page_url', 'user')) !!}
                                        @endif
                                    @endif
								@endforeach
							@else
								<p class="no-posts">{{ trans('messages.no_posts') }}</p>
							@endif
						</div>
                        <?php
                             $twoColumn = true;   
                        ?>
						<div class="timeline-posts timeline-condensed-column row" style="display: none">
							@if(count($posts) > 0)
								@foreach($posts as $post)
                                    @if($post->type == \App\Post::PAID_TYPE)
                                        @if($post->user->activeSubscribers->contains(Auth::user()->id)  || $post->user->id == Auth::user()->id)
                                            {!! Theme::partial('post_condensed_column',compact('post','timeline','next_page_url', 'user','twoColumn')) !!}
                                        @endif
                                    @else
                                        @if(canUserSeePost(Auth::id(), $post->user->id, $user->timeline->id) || $post->type == \App\Post::PRICE_TYPE)
                                        {!! Theme::partial('post_condensed_column',compact('post','timeline','next_page_url', 'user','twoColumn')) !!}
                                        @endif
                                    @endif
								@endforeach
							@else
								<p class="no-posts">{{ trans('messages.no_posts') }}</p>
							@endif
						</div>
					</div><!-- /col-md-8 -->
				</div><!-- /main-content -->
			</div><!-- /row -->
		</div><!-- /col-md-10 -->
{{--		<div class="col-md-2">--}}
{{--			{!! Theme::partial('timeline-rightbar') !!}--}}
{{--		</div>--}}
	</div>
</div><!-- /container -->
<script type="text/javascript">
    $('.switch-layout').click(function () {
        $('.timeline-default, .timeline-condensed-column').toggle();
        condensedLayout = !condensedLayout;
        twoColumn = !twoColumn;
        $.each($(".timeline-condensed-column>.panel"), function (i) {
            $(this).wrap('<div class="col-lg-6"></div>');
        });
        $('.switch-layout.three-column, .switch-layout.one-column').parent().toggle();
        if (!condensedLayout) {
            $.each($(".timeline-default>.col-lg-6 .panel"), function (i) {
                $(this).unwrap();
            });
        }
    });
</script>