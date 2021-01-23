<!-- main-section -->
	<!-- <div class="main-content"> -->
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

                    <div class="explore-posts">
                        @if($post->count() > 0)
                            <div class="row">
                                {!! Theme::partial('post',compact('post','timeline')) !!}
                            </div>    
                        @else
                            <div class="no-posts alert alert-warning">{{ trans('common.no_posts') }}</div>
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
        $('.explore-posts .row').jscroll({
            // loadingHtml: '<img src="loading.gif" alt="Loading" /> Loading...',
            nextSelector: 'a.jscroll-next:last',
            callback : function()
            {
                emojify.run();
                hashtagify();
                mentionify();
                $('.post-description').linkify()
                jQuery("time.timeago").timeago();
            }
        });
    };
</script>
