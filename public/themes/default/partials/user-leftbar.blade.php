

@if (        
        ($timeline->type == 'user' && $timeline->id == Auth::user()->timeline_id) ||
        ($timeline->type == 'page' && $timeline->page->is_admin(Auth::user()->id) == true) ||
        ($timeline->type == 'group' && $timeline->groups->is_admin(Auth::user()->id) == true)
        )
@endif

<div class="user-bio-block follow-links">
    
    @if($user->id != Auth::user()->id)
    <div class="online">
        <div class="bio-header">Status</div>
	    <div class="bio-description">
            @if($user->is_online)
		    <span style="color: #38a169;">Online Now</span>
            @elseif(isset($user->last_logged))
            Last seen {{ \Carbon\Carbon::parse($user->last_logged)->diffForHumans() }}    
            @endif
            @if(canMessageToUser(Auth::user(), $user))
    			<a style="margin-top:10px;" href="#" class="btn btn-options btn-block btn-default" onClick="chatBoxes.sendMessage({{ $timeline->user->id }})">
    				<i class="fa fa-inbox"></i> {{ trans('common.message') }}
    			</a>
            @endif
	    </div>
    </div>
    @endif

    @if($user->is_follow_for_free && $user->id != Auth::user()->id)
    <div class="follow">
        <div class="bio-header">Follow</div>
	    <div class="bio-description">
            @if(!$user->followers->contains(Auth::user()->id))
            <div class="follow-btn">
                <a href="javascript:void(0);" class="btn btn-options btn-block follow-user btn-default follow" data-price="{{ $user->price }}" data-follow="1"  data-timeline-id="{{ $timeline->id }}">
                    Follow for Free
                </a>
            </div>
            <div class="unfollow-btn hidden">
                <a href="javascript:void(0);" class="btn btn-options btn-block btn-success unfollow" data-price="{{ $user->price }}" data-follow="1"  data-timeline-id="{{ $timeline->id }}">
                    Follow for Free
                </a>
            </div>
            @else
                <div class="follow-btn hidden">
                    <a href="javascript:void(0);" class="btn btn-options btn-block follow-user btn-default follow" data-price="{{ $user->price }}" data-follow="1"  data-timeline-id="{{ $timeline->id }}">
                        Follow for Free
                    </a>
                </div>
                <div class="unfollow-btn">
                    <a href="javascript:void(0);" class="btn btn-options btn-block btn-success unfollow" data-price="{{ $user->price }}" data-follow="1"  data-timeline-id="{{ $timeline->id }}">
                        Unfollow
                    </a>
                </div>
            @endif
	    </div>
    </div>
    @endif
    
        @if($user->id != Auth::user()->id)
    <div class="subscribe">
        <div class="bio-header">Subscribe</div>
	    <div class="bio-description">
			@if(!$user->followers->contains(Auth::user()->id))
				<div class="left-col">
					<a href="javascript:void(0);" class="btn btn-options btn-block follow-user btn-default follow" data-price="{{ $user->price }}"  data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-heart"></i> {{ trans('common.follow') }}
					</a>
				</div>
				<div class="hidden">
					<a href="#" class="btn btn-options btn-block btn-success unfollow" data-price="{{ $user->price }}"  data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-check"></i> {{ trans('common.following') }}
					</a>
				</div>
			@else
				<div class="hidden">
					<a href="#" class="btn btn-options btn-block follow-user btn-default follow " data-price="{{ $user->price }}" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-heart"></i> {{ trans('common.follow') }}
					</a>
				</div>
				<div class="left-col">
					<a href="#" class="btn btn-options btn-block btn-success unfollow" data-price="{{ $user->price }}"  data-timeline-id="{{ $timeline->id }}">	
					    <i class="fa fa-check"></i> {{ trans('common.following') }}
					</a>
				</div>
			@endif
	    </div>
    </div>
@endif

        @if($user->id != Auth::user()->id)
            <div class="subscribe">
                <div class="bio-header"> {{ trans('common.send_tip') }}</div>
                <div class="bio-description">
                    <div class="left-col">
                        <a href="#" class="btn btn-options btn-block btn-default" data-toggle="modal" data-target="#sendTipModal">
                            <i class="fa fa-dollar"></i> {{ trans('common.send_tip') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    
	<!--<div class="bio-header">{{ trans('common.bio') }}</div>-->
	<div class="bio-description">
		{{ ($user->about != NULL) ? $user->about : trans('messages.no_description') }}
	</div>
	<a target="_blank" href="{{ $user->wishlist }}">{{ $user->wishlist }}</a><br>
	<a target="_blank" href="{{ $user->website }}">{{ $user->website }}</a><br>
	<a target="_blank" href="{{ $user->instagram }}">{{ $user->instagram }}</a>
	
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
		@if($user->country != NULL)
		<li>
			<i class="fa fa-map-marker" aria-hidden="true" style="margin-right: 3px;"></i><span>{{ trans('common.lives_in').' '.$user->country }}</span>
		</li>
		@endif

		@if($user->city != NULL)
		<li><i class="fa fa-building-o"></i><span>{{ trans('common.from').' '.$user->city }}</span></li>
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
