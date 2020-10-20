<div class="right-side-section">

	<div class="panel panel-default">
		<div class="panel-body nopadding">
			<div class="mini-profile fans">
				<div class="background">
					<div class="widget-bg">
					    <a href="{{ url(Auth::user()->username) }}">
						    <img src=" @if(Auth::user()->cover) {{ url('user/cover/'.Auth::user()->cover) }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}">
					    </a>
					</div>
					<div class="avatar-img">
					    <a href="{{ url(Auth::user()->username) }}">	
						    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}">
					    </a>
					</div>
				</div>
				<div class="avatar-profile">
					<div class="avatar-details">
						<h2 class="avatar-name">
							<a href="{{ url(Auth::user()->username) }}">
								{{ Auth::user()->name }}
							</a>
                            @if(Auth::user()->verified)
                                <svg width=".7em" height=".7em" viewBox="0 0 16 16" class="bi bi-patch-check-fll" fill="#298ad3" xmlns="http://www.w3.org/2000/svg">
                                  <path fill-rule="evenodd" d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984a.5.5 0 0 0-.708-.708L7 8.793 5.854 7.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/>
                                </svg>
                            @endif
						</h2>
						<h4 class="avatar-mail">
							<a href="{{ url(Auth::user()->username) }}">
								{{ '@'.Auth::user()->username }}
							</a>
						</h4>
					</div>      
				</div>
				<ul class="activity-list list-inline">
					<li>
						<a href="{{ url(Auth::user()->username.'/posts') }}">
							<div class="activity-name">
								{{ trans('common.posts') }}
							</div>
							<div class="activity-count">
								{{ count(Auth::user()->posts()->where('active', 1)->get()) }}
							</div>
						</a>
					</li>
					<li>
						<a href="{{ url(Auth::user()->username.'/followers') }}">
							<div class="activity-name">
								{{ trans('common.followers') }} 
							</div>
							<div class="activity-count">
								{{ Auth::user()->followers->count() }}
							</div>
						</a>
					</li>
					<li>
						<a href="{{ url(Auth::user()->username.'/following') }}">
							<div class="activity-name">
								{{ trans('common.following') }}
							</div>
							<div class="activity-count">
								{{ Auth::user()->following->count() }}
							</div>
						</a>
					</li>
					<li>
						<a href="{{ url(Auth::user()->username.'/settings/addbank') }}">
							<div class="activity-name">
								Earnings
							</div>
							<div class="activity-count">
								$1,500
							</div>
						</a>
					</li>
				</ul>
			</div><!-- /mini-profile -->							
		</div>
	</div><!-- /panel -->
	
	<div class="panel panel-default">
		<div class="panel-heading no-bg">
			<h3 class="panel-title">
				{{ trans('common.suggested_people') }}
			</h3>
		</div>
		<div class="panel-body">
			<!-- widget holder starts here -->
			<div class="user-follow fans">
				@if($suggested_users != "")
				@foreach($suggested_users as $suggested_user)
				<div class="media">
				
				<div class="mini-profile fans">
    				<div class="background">
    					<div class="widget-bg">
    					    <a href="{{ url($suggested_user->username) }}">
    						    <img src=" @if($suggested_user->cover) {{ url('user/cover/'.$suggested_user->cover) }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
    						</a>
    					</div>
    					<div class="avatar-img">
    						<a href="{{ url($suggested_user->username) }}">
    						    <img src="{{ $suggested_user->avatar }}" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
    						</a>
    					</div>
    				</div>
    				<div class="avatar-profile">
    					<div class="avatar-details">
    						<h2 class="avatar-name">
    							<a href="{{ url($suggested_user->username) }}">
    								{{ $suggested_user->name }}
    							</a>
                                @if($suggested_user->verified)
                                    <span class="verified-badge bg-success">
                                        <i class="fa fa-check"></i>
                                    </span>
                                @endif
    						</h2>
    						<h4 class="avatar-mail">
    							<a href="{{ url($suggested_user->username) }}">
    								{{ '@'.$suggested_user->username }}
    							</a>
    						</h4>
    					</div>      
    				</div>
    			</div>
    			<!--	
					<div class="media-left badge-verification">
						<a href="{{ url($suggested_user->username) }}">
							<img src="{{ $suggested_user->avatar }}" class="img-icon" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
							@if($suggested_user->verified)
							<span class="verified-badge bg-success verified-medium">
								<i class="fa fa-check"></i>
							</span>
							@endif
						</a>
					</div>
					<div class="media-body socialte-timeline follow-links">
						<h4 class="media-heading"><a href="{{ url($suggested_user->username) }}">{{ $suggested_user->name }} </a>
							<span class="text-muted">{{ '@'.$suggested_user->username }}</span>
						</h4>
{{--						@if($suggested_user->payment != NULL && $suggested_user->payment->is_active == 1 && $suggested_user->payment->price > 0)--}}
						@if($suggested_user->price >= 0)
							<div class="btn-follow">
								<a href="#" class="btn btn-default follow-user follow" data-price="{{ $suggested_user->price }}" data-timeline-id="{{ $suggested_user->timeline->id }}"> <i class="fa fa-heart"></i> {{ trans('common.follow') }}</a>
							</div>
							<div class="btn-follow hidden">
								<a href="#" class="btn btn-success follow-user unfollow" data-price="{{ $suggested_user->price }}" data-timeline-id="{{ $suggested_user->timeline->id }}"><i class="fa fa-check"></i> {{ trans('common.following') }}</a>
							</div>
						@endif
						</div>
                    -->
					</div>
					@endforeach
					@else
					<div class="alert alert-warning">
						{{ trans('messages.no_suggested_users') }}
					</div>
					@endif

				</div>
				<!-- widget holder ends here -->
			</div>
		</div>
		</div>