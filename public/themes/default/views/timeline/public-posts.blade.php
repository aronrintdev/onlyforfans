<style>

	.favourite-grid {
		margin: 0 -7px;
	}

	.favourite-grid .img {
		background: #ccc;
		height: 100px;
		margin-bottom: 14px;
	}
	
	.favourite-grid .locked-content {
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 24px;
		color: #fff;
	}
	
	@media screen and (max-width: 1024px) {
		.favourite-grid .img {
			height: 200px;
		}
	}

	.favourite-grid > div {
		padding: 0 7px;
	}

	.timeline-posts .post-image-holder a {
		position: relative;
	}
	
	.timeline-posts .post-image-holder a:after {
		content: "\f023";
		position: absolute;
		top: 50%;
		transform: translate(-50%, -50%);
		left: 50%;
		color: #fff;
		display: inline-block;
		font: normal normal normal 24px/1 FontAwesome;
		font-size: 50px;
		text-rendering: auto;
		-webkit-font-smoothing: antialiased;
	}
</style>
<!-- main-section -->
<div class="container">
	<div class="row">
		<div class="col-md-12">
			{!! Theme::partial('public-user-header',compact('timeline', 'liked_post', 'liked_pages','user','joined_groups','followRequests','following_count',
			'followers_count','follow_confirm','user_post','joined_groups_count','guest_events')) !!}
			<div class="row">
				<div class=" timeline">
					<div class="col-md-4">
						{!! Theme::partial('public-user-leftbar',compact('timeline','user','follow_user_status','own_groups','own_pages','user_events')) !!}
					</div>
					<div class="col-md-8">
						<div class="timeline-posts">
							@if(count($posts) > 0)
								@foreach($posts as $post)
									{!! Theme::partial('public-post',compact('post','timeline','next_page_url', 'user')) !!}
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

{{ \Illuminate\Support\Facades\Session::put('users.profile', Request::path()) }}

