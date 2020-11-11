<div class="user-profile-buttons">
	<div class="row follow-links pagelike-links">
		<!-- This [if-1] is for checking current user timeline or diff user timeline -->	
		<?php 
		//php code is for checking user's follow_privacy settings
		$user_follow ="";
		$confirm_follow ="";
		$message_privacy ="";						
		$othersSettings = $user->getOthersSettings($timeline->username);
		if($othersSettings)
		{
			//follow_privacy checking
			if ($othersSettings->follow_privacy == "only_follow") {
				$user_follow = "only_follow";
			}elseif ($othersSettings->follow_privacy == "everyone") {
				$user_follow = "everyone";
			}

			//confirm_follow checking
			if ($othersSettings->confirm_follow == "yes") {
				$confirm_follow = "yes";
			}elseif ($othersSettings->confirm_follow == "no") {
				$confirm_follow = "no";
			}

			//message_privacy checking
			if ($othersSettings->message_privacy == "only_follow") {
				$message_privacy = "only_follow";
			}elseif ($othersSettings->message_privacy == "everyone") {
				$message_privacy = "everyone";
			}
		}

		?>
			<!-- This [if-3] is for checking usersettings follow_privacy showing follow/following || message button -->
			<!--@if($user->price >= 0)-->

				<!--@if(Auth::check() || !$user->followers->contains($user->id))-->

{{--						<div class="col-md-6 col-sm-6 col-xs-6 left-col">--}}
{{--							<a href="{{ url('/login') }}" class="btn btn-options btn-block btn-default follow" data-price="{{ $user->price }}"  data-timeline-id="{{ $timeline->id }}">--}}
{{--								<i class="fa fa-heart"></i> {{ trans('common.follow') }}--}}
{{--							</a>--}}
{{--						</div>--}}

{{--						<div class="col-md-6 col-sm-6 col-xs-6 hidden">--}}
{{--							<a href="#" class="btn btn-options btn-block btn-success unfollow" data-price="{{ $user->price }}"  data-timeline-id="{{ $timeline->id }}">--}}
{{--								<i class="fa fa-check"></i> {{ trans('common.following') }}--}}
{{--							</a>--}}
{{--						</div>--}}
{{--				<!--@else-->--}}

{{--					<div class="col-md-6 col-sm-6 col-xs-6 hidden">--}}
{{--						<a href="#" class="btn btn-options btn-block btn-success follow " data-price="{{ $user->price }}" data-timeline-id="{{ $timeline->id }}">--}}
{{--							<i class="fa fa-heart"></i> {{ trans('common.follow') }}--}}
{{--						</a>--}}
{{--					</div>--}}

{{--					<div class="col-md-6 col-sm-6 col-xs-6 left-col">--}}
{{--						<a href="#" class="btn btn-options btn-block btn-success unfollow" data-price="{{ $user->price }}"  data-timeline-id="{{ $timeline->id }}">	<i class="fa fa-check"></i> {{ trans('common.following') }}--}}
{{--						</a>--}}
{{--					</div>--}}
				<!--@endif-->

			<!--@endif	<!-- End of [if-3]-->
				<!--<div class="col-md-6 col-sm-6 col-xs-6 right-col">-->
				<!--	<a href="#" class="btn btn-options btn-block btn-success" onClick="chatBoxes.sendMessage({{ $timeline->user->id }})">-->
				<!--		<i class="fa fa-inbox"></i> {{ trans('common.message') }}-->
				<!--	</a>-->
				<!--</div>-->

	</div>
</div>

@if (        
        ($timeline->type == 'user' && $timeline->id == $user->timeline_id) ||
        ($timeline->type == 'page' && $timeline->page->is_admin($user->id) == true) ||
        ($timeline->type == 'group' && $timeline->groups->is_admin($user->id) == true)
        )
@endif

@if($user->about != NULL)
<div class="user-bio-block follow-links">

	<div class="bio-description">
		<a href="{{ url('login') }}" class="btn btn-submit btn-success" style="display:block; margin-top:10px;">
			<i class="fa fa-inbox"></i> {{ trans('common.message') }}
		</a>
	</div>
	@if($user->is_follow_for_free)
	<div class="follow">
		<div class="bio-header">Follow</div>
		<div class="bio-description">
			<div class="follow-btn">
				<a href="{{ url('login') }}" class="btn btn-submit btn-success" style="display:block;">
					Follow for Free
				</a>
			</div>
		</div>
	</div>
	@endif
	<div class="subscribe">
		<div class="bio-header">Subscribe</div>
		<div class="bio-description">
			<div class="left-col">
				<a href="{{ url('login') }}" class="btn btn-submit btn-success" style="display:block;">
					<i class="fa fa-heart"></i> {{ trans('common.follow') }}
				</a>
			</div>
		</div>
	</div>
	<div class="subscribe">
		<div class="bio-header"> {{ trans('common.send_tip') }}</div>
		<div class="bio-description">
			<div class="left-col">
				<a href="{{ url('login') }}" class="btn btn-submit btn-success" style="display:block;">
					<i class="fa fa-dollar"></i> {{ trans('common.send_tip') }}
				</a>
			</div>
		</div>
	</div>

	<div class="bio-description">
		{{ ($user->about != NULL) ? $user->about : trans('messages.no_description') }}
	</div>
	@if($user->wishlist)
		<a target="_blank" href="{{ $user->wishlist }}">{{ $user->wishlist }}</a><br>
	@endif
	@if($user->website)
		<a target="_blank" href="{{ $user->website }}">{{ $user->website }}</a><br>
	@endif
	@if($user->instagram)
		<a target="_blank" href="{{ $user->instagram }}">{{ $user->instagram }}</a><br>
	@endif

		<ul class="list-unstyled list-details">
		@if($user->hobbies != NULL)
			<li><i class="fa fa-chain" aria-hidden="true"></i> {{ $user->hobbies }}</li>
		@endif
		@if($user->interests != NULL)
			<li><i class="fa fa-instagram" aria-hidden="true"></i> {{ $user->interests }}</li>
		@endif
		@if($user->custom_option1 != NULL && Setting::get('custom_option1') != NULL)
			<li>{!! '<b>'.Setting::get('custom_option1').': </b>'!!} {{ $user->custom_option1 }}</li>
		@endif
		@if($user->custom_option2 != NULL && Setting::get('custom_option2') != NULL)
			<li>{!! '<b>'.Setting::get('custom_option2').': </b>'!!} {{ $user->custom_option2 }}</li>
		@endif
		@if($user->custom_option3 != NULL && Setting::get('custom_option3') != NULL)
			<li>{!! '<b>'.Setting::get('custom_option3').': </b>'!!} {{ $user->custom_option3 }}</li>
		@endif
		@if($user->custom_option4 != NULL && Setting::get('custom_option4') != NULL)
			<li>{!! '<b>'.Setting::get('custom_option4').': </b>'!!} {{ $user->custom_option4 }}</li>
		@endif
	</ul>
	
	<ul class="list-unstyled list-details">
		@if($user->designation != NULL)
			<li><i class="fa fa-thumb-tack"></i> <span>{{ $user->designation }}</span></li>
		@endif
		@if($user->city != NULL)
		<li>
			<svg style="margin-right:3px;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-geo-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999zm2.493 8.574a.5.5 0 0 1-.411.575c-.712.118-1.28.295-1.655.493a1.319 1.319 0 0 0-.37.265.301.301 0 0 0-.057.09V14l.002.008a.147.147 0 0 0 .016.033.617.617 0 0 0 .145.15c.165.13.435.27.813.395.751.25 1.82.414 3.024.414s2.273-.163 3.024-.414c.378-.126.648-.265.813-.395a.619.619 0 0 0 .146-.15.148.148 0 0 0 .015-.033L12 14v-.004a.301.301 0 0 0-.057-.09 1.318 1.318 0 0 0-.37-.264c-.376-.198-.943-.375-1.655-.493a.5.5 0 1 1 .164-.986c.77.127 1.452.328 1.957.594C12.5 13 13 13.4 13 14c0 .426-.26.752-.544.977-.29.228-.68.413-1.116.558-.878.293-2.059.465-3.34.465-1.281 0-2.462-.172-3.34-.465-.436-.145-.826-.33-1.116-.558C3.26 14.752 3 14.426 3 14c0-.599.5-1 .961-1.243.505-.266 1.187-.467 1.957-.594a.5.5 0 0 1 .575.411z"/>
            </svg>
            <span>{{ $user->city }}</span>
		</li>
		@endif
		@if($user->country != NULL)
		<li>
		    <svg style="margin-right:4px;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-globe" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4H2.255a7.025 7.025 0 0 1 3.072-2.472 6.7 6.7 0 0 0-.597.933c-.247.464-.462.98-.64 1.539zm-.582 3.5h-2.49c.062-.89.291-1.733.656-2.5H3.82a13.652 13.652 0 0 0-.312 2.5zM4.847 5H7.5v2.5H4.51A12.5 12.5 0 0 1 4.846 5zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5H7.5V11H4.847a12.5 12.5 0 0 1-.338-2.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12H7.5v2.923c-.67-.204-1.335-.82-1.887-1.855A7.97 7.97 0 0 1 5.145 12zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11H1.674a6.958 6.958 0 0 1-.656-2.5h2.49c.03.877.138 1.718.312 2.5zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12h2.355a7.967 7.967 0 0 1-.468 1.068c-.552 1.035-1.218 1.65-1.887 1.855V12zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.65 13.65 0 0 1-.312 2.5zm2.802-3.5h-2.49A13.65 13.65 0 0 0 12.18 5h2.146c.365.767.594 1.61.656 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4H8.5V1.077c.67.204 1.335.82 1.887 1.855.173.324.33.682.468 1.068z"/>
            </svg>
            <span>{{ $user->country }}</span>
        </li>
		@endif

{{--		@if($user->birthday != '1970-01-01')--}}
{{--		<li><i class="fa fa-calendar"></i><span>--}}
{{--			{{ trans('common.born_on').' '.date('F d', strtotime($user->birthday)) }}--}}
{{--		</span></li>--}}
{{--		@endif--}}
	</ul>
	<ul class="list-inline list-unstyled social-links-list">
		@if($user->facebook_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->facebook_link }}" class="btn btn-facebook"><i class="fa fa-facebook"></i></a>
			</li>
		@endif
		@if($user->twitter_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->twitter_link }}" class="btn btn-twitter"><i class="fa fa-twitter"></i></a>
			</li>
		@endif
		@if($user->dribbble_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->dribbble_link }}" class="btn btn-dribbble"><i class="fa fa-dribbble"></i></a>
			</li>
		@endif
		@if($user->youtube_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->youtube_link }}" class="btn btn-youtube"><i class="fa fa-youtube"></i></a>
			</li>
		@endif
		@if($user->instagram_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->instagram_link }}" class="btn btn-instagram"><i class="fa fa-instagram"></i></a>
			</li>
		@endif
		@if($user->linkedin_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->linkedin_link }}" class="btn btn-linkedin"><i class="fa fa-linkedin"></i></a>
			</li>
		@endif
	</ul>
</div>
@endif
<form class="change-avatar-form hidden" action="{{ url('ajax/change-avatar') }}" method="post" enctype="multipart/form-data">
	<input name="timeline_id" value="{{ $timeline->id }}" type="hidden">
	<input name="timeline_type" value="{{ $timeline->type }}" type="hidden">
	<input class="change-avatar-input hidden" accept="image/jpeg,image/png" type="file" name="change_avatar" >
</form>
<form class="change-cover-form hidden" action="{{ url('ajax/change-cover') }}" method="post" enctype="multipart/form-data">
	<input name="timeline_id" value="{{ $timeline->id }}" type="hidden">
	<input name="timeline_type" value="{{ $timeline->type }}" type="hidden">
	<input class="change-cover-input hidden" accept="image/jpeg,image/png" type="file" name="change_cover" >
</form>
	@if(Setting::get('timeline_ad') != NULL)
	<div id="link_other" class="post-filters">
		{!! htmlspecialchars_decode(Setting::get('timeline_ad')) !!} 
	</div>	
	@endif
	<div class="favourite-grid row">
		<div class="col-sm-4 col-6">
			<div class="img">
				<div class="locked-content">
					<i class="fa fa-lock" aria-hidden="true"></i>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-6">
			<div class="img">
				<div class="locked-content">
					<i class="fa fa-lock" aria-hidden="true"></i>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-6">
			<div class="img">
				<div class="locked-content">
					<i class="fa fa-lock" aria-hidden="true"></i>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-6">
			<div class="img">
				<div class="locked-content">
					<i class="fa fa-lock" aria-hidden="true"></i>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-6">
			<div class="img">
				<div class="locked-content">
					<i class="fa fa-lock" aria-hidden="true"></i>
				</div>
			</div>
		</div>
		<div class="col-sm-4 col-6">
			<div class="img">
				<div class="locked-content">
					<i class="fa fa-lock" aria-hidden="true"></i>
				</div>
			</div>
		</div>
	</div>