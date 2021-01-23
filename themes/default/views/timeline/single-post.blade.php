<!-- main-section -->
	<!-- <div class="main-content"> -->
		<div class="container">
			<div class="row">
{{--				<div class="visible-lg col-lg-2">--}}
{{--					{!! Theme::partial('home-leftbar',compact('trending_tags')) !!}--}}
{{--				</div>--}}
              
                <div class="col-lg-12">

					<div class="timeline-posts">
						@if($mode == 'posts')
							{!! Theme::partial('post',compact('post','timeline')) !!}
						@elseif($mode == 'notifications')
							{!! Theme::partial('allnotifications',compact('notifications')) !!}
						@endif							
					</div>
				</div>
			</div>
		</div>
	<!-- </div> -->
<!-- /main-section -->
