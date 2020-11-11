<div class="timeline-cover-section">
	<div class="timeline-cover">
		<ul class="list-inline pagelike-links">							
			@if(!$user->timeline->albums->isEmpty())
				<li class=""><a href="" > {{ trans('common.photos') }}</span></a></li>
			@endif
			@if($user->username != $timeline->username)
				@if(!$timeline->reports->contains($user->id))
				<li class="pull-right">
					<a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i> {{ trans('common.report') }}
					</a>
				</li>
				<li class="hidden pull-right">
					<a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>	{{ trans('common.reported') }}
					</a>
				</li>
				@else
				<li class="hidden pull-right">
					<a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i> {{ trans('common.report') }}
					</a>
				</li>
				<li class="pull-right">
					<a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>	{{ trans('common.reported') }}
					</a>
				</li>
				@endif
			@endif
			@if($user->username != $timeline->username)
				@if(!$timeline->reports->contains($user->id))
					<li class="smallscreen-report"><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a></li>
					<li class="hidden smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a></li>
				@else
					<li class="hidden smallscreen-report"><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a></li>
					<li class="smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a></li>
				@endif
			@endif
		</ul>
	    
		<img src=" @if($timeline->cover_id) {{ url('user/cover/'.$timeline->cover->source) }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
{{--		@if($timeline->id == $user->timeline_id)--}}
{{--			<a href="#" class="btn btn-camera-cover change-cover"><i class="fa fa-camera" aria-hidden="true"></i><span class="change-cover-text">{{ trans('common.change_cover') }}</span></a>--}}
{{--		@endif--}}
		<div class="user-cover-progress hidden">
		    
		</div>
		<!-- <div class="cover-bottom"></div> -->
	</div>
	<div class="timeline-list">
			<!-- <div class="status-button">
					<a href="#" class="btn btn-status">Subscription Packages</a>
			</div> -->
		<div class="user-timeline-name">
			<a href="">{{ $timeline->name }}</a><br><a class="user-timeline-username" href="{{ url($timeline->username) }}">{{ $timeline->username }}</a>
				{!! verifiedBadge($timeline) !!}
		</div>
		<span class="status status-holder-{{ $user->id }} {{ $user->last_logged ? '' : 'text-success' }}">{{ $user->last_logged ? 'Last seen '.\Carbon\Carbon::parse($user->last_logged)->timezone(Auth::user()->timezone)->diffForHumans() : 'Online Now' }}</span>
		<ul class="list-inline pagelike-links">							
			<li class="timeline-cover-status {{ Request::segment(2) == 'posts' ? 'active' : '' }}">
			    <a href="{{ url($timeline->username.'/posts') }}" ><span class="top-list">{{ count($timeline->posts()->where('active', 1)->get()) }} <svg data-toggle="tooltip" data-placement="bottom" data-original-title="Posts" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-bookmarks-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M2 4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v11.5a.5.5 0 0 1-.777.416L7 13.101l-4.223 2.815A.5.5 0 0 1 2 15.5V4z"/>
                  <path fill-rule="evenodd" d="M4.268 1H12a1 1 0 0 1 1 1v11.768l.223.148A.5.5 0 0 0 14 13.5V2a2 2 0 0 0-2-2H6a2 2 0 0 0-1.732 1z"/>
                </svg></span></a>
            </li>
			<li class="timeline-cover-status {{ Request::segment(2) == 'followers' ? 'active' : '' }}">
				<a href="{{ url($timeline->username.'/followers') }}" ><span class="top-list">{{ $followers_count }}  <svg data-toggle="tooltip" data-placement="bottom" data-original-title="Fans" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-people-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
                    </svg></span>
				</a>
			</li>
			<li class="timeline-cover-status {{ Request::segment(2) == 'liked' ? 'active' : '' }}">
				<a href="#" ><span class="top-list"><span class="liked-post">{{count($liked_post)}}</span> <svg data-toggle="tooltip" data-placement="bottom" data-original-title="Likes" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-suit-heart-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                      <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1z"/>
                    </svg></span>
				</a>
			</li>
			<li class="timeline-cover-status {{ Request::segment(2) == 'following' ? 'active' : '' }}">
				<a href="{{ url($timeline->username.'/following') }}" ><span class="top-list">{{ $following_count }}  <svg data-toggle="tooltip" data-placement="bottom" data-original-title="Following" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-person-check-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm9.854-2.854a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                    </svg></span>
				</a>
			</li>
		</ul>
		<div class="timeline-user-avtar">
			<img src="{{ $timeline->user->avatar }}" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
{{--				@if($timeline->id == $user->timeline_id)--}}
{{--					<div class="chang-user-avatar">--}}
{{--						<a href="#" class="btn btn-camera change-avatar"><i class="fa fa-camera" aria-hidden="true"></i><span class="avatar-text">{{ trans('common.update_profile') }}<span>{{ trans('common.picture') }}</span></span></a>--}}
{{--					</div>--}}
{{--				@endif			--}}
			<div class="user-avatar-progress hidden">
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	@if($timeline->background_id != NULL)
		$('body')
			.css('background-image', "url({{ url('/wallpaper/'.$timeline->wallpaper->source) }})")
			.css('background-attachment', 'fixed');
	@endif
</script>