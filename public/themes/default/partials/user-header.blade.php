<style>
    .smallscreen-report, a.page-report.report {
        display: block !important;
    }
    
    @media screen and (max-width: 767px) {
        .user-image.dropdown.fans .dropdown-menu {
            left: -190px;
        }
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
</style>
<div class="timeline-cover-section">
	<div class="timeline-cover">
		<div class="profile-dropdown-menu">
			<ul class="list-inline no-margin">
				<li class="dropdown">
					<a href="#" class="dropdown-togle profile-dropdown-icon" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<svg style="margin-right:15px;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-three-dots-vertical" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                        </svg>
					</a>
					<ul class="dropdown-menu profile-dropdown-menu-content pagelike-links">
						<li class="main-link">
							@if($timeline->id == Auth::user()->timeline_id)
								<a href="{{ url('/'.Auth::user()->username.'/settings/profile') }}">
									{{ trans('common.edit_profile') }}
								</a>
							@endif
							<input type="hidden" id="profile-url" value="{{ url($timeline->username) }}">
							<a href="#" onclick="getProfileUrl(); return false;">
								{{ trans('common.copy_link_to_profile') }}
							</a>
							@if(Auth::user()->username != $timeline->username)
								<a href="#" id="link-list-modal" data-toggle="modal" data-target="#listsModal">
									{{ trans('common.add_to_remove_from_lists') }}
								</a>
							@endif
                        @if(Auth::user()->username != $timeline->username)
                            @if(!$timeline->reports->contains(Auth::user()->id))
                                <li class="timeline-cover-status main-link">
                                    <a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a>
                                </li>
                                <li class="timeline-cover-status main-link hidden">
                                    <a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a>
                                </li>
                                <li class="timeline-cover-status main-link">
                                    <a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.block') }}</a>
                                </li>
                                <li class="timeline-cover-status main-link hidden">
                                    <a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.blocked') }}</a>
                                </li>
                            @else
                                <li class="timeline-cover-status main-link hidden">
                                    <a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a>
                                </li>
                                <li class="timeline-cover-status main-link">
                                    <a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a>
                                </li>
                                <li class="timeline-cover-status main-link hidden">
                                    <a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.block') }}</a>
                                </li>
                                <li class="timeline-cover-status main-link">
                                    <a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.blocked') }}</a>
                                </li>
                                @endif
                            @endif
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<img src=" @if($timeline->cover_id) {{ url('user/cover/'.$timeline->cover->source) }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
		@if($timeline->id == Auth::user()->timeline_id)
			<a href="#" class="btn btn-camera-cover change-cover">
    			<svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-camera-fill" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                  <path fill-rule="evenodd" d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/>
                </svg><span class="change-cover-text">{{ trans('common.change_cover') }}</span></a>
		@endif
		<div class="user-cover-progress hidden">
		    
		</div>
			<!-- <div class="cover-bottom">	</div> -->
	</div>
	<div class="timeline-list">
		<div class="user-timeline-name">
			<a href="{{ url($timeline->username) }}">{{ $timeline->name }}</a><br><a class="user-timeline-username" href="{{ url($timeline->username) }}"><span>@</span>{{ $timeline->username }}</a>
				{!! verifiedBadge($timeline) !!}
		</div>
		<span class="status status-holder-{{ $user->id }} {{ $user->last_logged ? '' : 'text-success' }}">{{ $user->last_logged ? 'Last seen '.\Carbon\Carbon::parse($user->last_logged)->timezone(Auth::user()->timezone)->diffForHumans() : 'Online Now' }}</span>
		<ul class="list-inline pagelike-links">
			{{--			@if($user_post == true)--}}
			<li class="timeline-cover-status {{ Request::segment(2) == 'posts' ? 'active' : '' }}">
			    <a href="{{ url($timeline->username.'/posts') }}" ><span class="top-list">{{ count($timeline->posts()->where('active', 1)->get()) }} <svg data-toggle="tooltip" data-placement="bottom" data-original-title="Posts" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-bookmarks-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M2 4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v11.5a.5.5 0 0 1-.777.416L7 13.101l-4.223 2.815A.5.5 0 0 1 2 15.5V4z"/>
                  <path fill-rule="evenodd" d="M4.268 1H12a1 1 0 0 1 1 1v11.768l.223.148A.5.5 0 0 0 14 13.5V2a2 2 0 0 0-2-2H6a2 2 0 0 0-1.732 1z"/>
                </svg></span></a>
            </li>

		{{--			@else--}}
		{{--				<li class="timeline-cover-status {{ Request::segment(2) == 'posts' ? 'active' : '' }}"><a href="#"><span class="top-list">{{ count($timeline->posts()->where('active', 1)->get()) }} {{ trans('common.posts') }}</span></a></li>--}}
		{{--			@endif--}}
		<!-- <li class="{{ Request::segment(2) == 'following' ? 'active' : '' }} smallscreen-report"><a href="{{ url($timeline->username.'/following') }}" ><span class="top-list">{{ $following_count }} {{ trans('common.following') }}</span></a></li>
		<li class="{{ Request::segment(2) == 'followers' ? 'active' : '' }} smallscreen-report"><a href="{{ url($timeline->username.'/followers') }}" ><span class="top-list">{{ $followers_count }}  {{ trans('common.followers') }}</span></a></li>-->

			@if(!$user->timeline->albums->isEmpty())
				<li class=""><a href="{{ url($timeline->username.'/albums') }}" > {{ trans('common.photos') }}</span></a></li>
			@endif
			
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
{{--			@if(Auth::user()->username != $timeline->username)--}}
{{--				@if(!$timeline->reports->contains(Auth::user()->id))--}}
{{--					<li class="smallscreen-report"><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a></li>--}}
{{--					<li class="hidden smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a></li>--}}
{{--				@else--}}
{{--					<li class="hidden smallscreen-report"><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a></li>--}}
{{--					<li class="smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a></li>--}}
{{--				@endif--}}
{{--			@endif--}}
		</ul>
		<div class="timeline-user-avtar">
			<img src="{{ $timeline->user->avatar }}" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
			@if($timeline->id == Auth::user()->timeline_id)
				<div class="chang-user-avatar">
					<a href="#" class="btn btn-camera change-avatar"><i class="fa fa-camera" aria-hidden="true"></i><span class="avatar-text">{{ trans('common.update_profile') }}<span>{{ trans('common.picture') }}</span></span></a>
				</div>
			@endif			
			<div class="user-avatar-progress hidden">
			</div>
		</div>
	</div>
</div>

<div id="listsModal" class="modal fade" role="dialog" tabindex='-1'>
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="modal-header lists-modal">
{{--				<button type="button" class="close" data-dismiss="modal">&times;</button>--}}
					<h3 class="modal-title lists-modal-title">
						<svg class="g-icon" aria-hidden="true">
							<use xlink:href="#icon-lists" href="#icon-lists">
								<svg id="icon-lists" viewBox="0 0 24 24"> <path d="M6.69 5H5.24L4.8 3.64a.31.31 0 00-.6 0L3.76 5H2.31a.31.31 0 00-.18.56l1.17.85-.45 1.39a.28.28 0 000 .09.31.31 0 00.31.32.3.3 0 00.18-.06L4.5 7.3l1.17.85a.3.3 0 00.18.06.31.31 0 00.31-.32.28.28 0 000-.09L5.7 6.42l1.17-.85A.31.31 0 006.69 5zm0 6H5.24L4.8 9.64a.31.31 0 00-.6 0L3.76 11H2.31a.31.31 0 00-.18.56l1.17.85-.45 1.39a.28.28 0 000 .09.31.31 0 00.31.32.3.3 0 00.18-.06l1.16-.85 1.17.85a.3.3 0 00.18.06.31.31 0 00.31-.32.28.28 0 000-.09l-.46-1.38 1.17-.85a.31.31 0 00-.18-.57zm0 6H5.24l-.44-1.36a.31.31 0 00-.6 0L3.76 17H2.31a.31.31 0 00-.18.56l1.17.85-.45 1.39a.28.28 0 000 .09.31.31 0 00.31.32.3.3 0 00.18-.06l1.16-.85 1.17.85a.3.3 0 00.18.06.31.31 0 00.31-.32.28.28 0 000-.09l-.46-1.38 1.17-.85a.31.31 0 00-.18-.57zM10 7h10a1 1 0 000-2H10a1 1 0 000 2zm10 4H10a1 1 0 000 2h10a1 1 0 000-2zm0 6H10a1 1 0 000 2h10a1 1 0 000-2z"></path> </svg>
							</use>
						</svg>
						{{ trans("common.save_to") }}
					</h3>
				</div>
				<div class="b-stats-row__content">
					<input hidden id="saved-user-id" name="saved-user-id" value="{{$timeline->user->id}}">
					@if (!empty($user_lists))
						@foreach ($user_lists as $user_list)
							<div class="modal-list-item">
								<input type="checkbox" class="red-checkbox" id="list-type-{{$user_list->id}}" name="list-type-{{$user_list->id}}" {{$user_list->state == 1 ? "checked" : ""}}>
								<label class="red-list-label" for="list-type-{{$user_list->id}}">{{$user_list->list_type}}</label>
							</div>
						@endforeach
					@endif
					<button type="button" class="g-btn" id="add-new-list"  data-toggle="modal" data-target="#newListModal">
						<svg class="g-icon" aria-hidden="true">
							<use xlink:href="#icon-add" href="#icon-add">
								<svg id="icon-add" viewBox="0 0 24 24"> <path d="M4.5 13H11v6.5a1 1 0 0 0 2 0V13h6.5a1 1 0 0 0 0-2H13V4.5a1 1 0 0 0-2 0V11H4.5a1 1 0 0 0 0 2z"></path> </svg>
							</use>
						</svg> New list
					</button>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
			</div>
		</div>
	</div>
</div>

<div id="newListModal" class="modal fade" role="dialog" tabindex='1'>
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="modal-header lists-modal">
					{{--						<button type="button" class="close" data-dismiss="modal">&times;</button>--}}
					<h3 class="modal-title lists-modal-title">
						<svg class="g-icon" aria-hidden="true">
							<use xlink:href="#icon-new-list" href="#icon-new-list">
								<svg id="icon-new-list" viewBox="0 0 24 24"> <path d="M22.32 9.31a1 1 0 00-.93-1l-6.17-.41-2.29-5.74a1 1 0 00-1.86 0L8.78 7.9l-6.17.41a1 1 0 00-.93 1 1 1 0 00.36.77L6.79 14l-1.52 6a1 1 0 001 1.24 1 1 0 00.53-.15l5.2-3.27 5.23 3.29a1 1 0 00.53.15 1 1 0 001-1 1 1 0 000-.24l-1.52-6L22 10.08a1 1 0 00.32-.77zm-6.87 3.59a1 1 0 00-.33 1l1.06 4.17-3.65-2.29a1 1 0 00-1.06 0l-3.65 2.3 1.06-4.17a1 1 0 00-.33-1l-3.31-2.77 4.3-.29a1 1 0 00.86-.62l1.6-4 1.6 4a1 1 0 00.86.62l4.3.29z"></path> </svg>
							</use>
						</svg>
						{{ trans("common.create_new_list") }}
					</h3>
				</div>
				<div class="b-stats-row__content">
					<input type="text" id="etNewListName" class="form-control" placeholder="Enter list name">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="cancelNewList" class="btn btn-default" data-dismiss="modal">{{ trans('common.cancel') }}</button>
				<button type="button" id="saveNewList" class="btn btn-primary" disabled>{{ trans('common.save') }}</button>
			</div>
		</div>

	</div>
</div>

<div id="sendTipModal" class="tip-modal modal fade subscriberFilterModal" role="dialog" tabindex='1'>
    <input type="hidden" value="{{$user->id}}" id="user-id">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body no-padding">
				<div class="panel panel-default panel-post animated" style="margin-bottom: 0">
					<div class="panel-heading no-bg">
						<h3 style="margin-top: 0; margin-bottom: 15px">Send Tip</h3>
						<button type="button" style="display: none;" class="close close-post-modal" data-dismiss="modal">&times;</button>
						<div class="post-author">
							<div class="user-avatar">
								<a href="{{ url($user->username) }}"><img src="{{ $user->avatar }}" alt="{{ $user->name }}" title="{{ $user->name }}"></a>
							</div>
							<div class="user-post-details">
								<ul class="list-unstyled no-margin">
									<li>
										<a href="{{ url($user->username) }}" title="{{ '@'.$user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">
											{{ $user->name }}
										</a>
										@if($user->verified)
											<span class="verified-badge bg-success">
                                                <i class="fa fa-check"></i>
                                            </span>
										@endif
									</li>
									<li>
										{{ '@'.$user->username }}
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="panel-body">
						<ul>
							<li>
								<p>Tip Amount:</p>
								<div class="total-tipped">
									<span class="decrement"><i class="fa fa-minus" aria-hidden="true"></i></span>
									<span class="increment"><i class="fa fa-plus" aria-hidden="true"></i></span>
									<input min="0" type="number" id="etTipAmount" value="10" data-id="2"  name="total_tipped" class="filter-input total-tipped-input  form-control etTipAmount">
									<div class="text-wrapper"></div>
								</div>	
							</li>
						</ul>
						<textarea name="tip_note" id="tipNote" cols="60" rows="5" style="width: 100%" placeholder="Include a message"></textarea>
					</div>
					<div class="panel-footer">
						<button type="button" id="cancelSendTip" class="btn btn-default" data-dismiss="modal">{{ trans('common.cancel') }}</button>
						@if(Auth::user()->is_payment_set)
							<button type="button" id="sendTip" class="btn btn-primary sendUserTip" disabled>{{ trans('common.send_tip') }}</button>
						@else
							<a href="{{url(Auth::user()->username).'/settings/addpayment' }}" id="addPayment" class="btn btn-warning">{{ trans('common.add_payment') }}</a>
						@endif
					</div>
				</div>
            </div>
        </div>
    </div>
</div>

{!! Theme::asset()->container('footer')->usePath()->add('currency', 'js/currency.min.js') !!}
<style>
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
<script type="text/javascript">
	$( document ).ready(function() {
	});
	@if($timeline->background_id != NULL)
		$('body')
			.css('background-image', "url({{ url('/wallpaper/'.$timeline->wallpaper->source) }})")
			.css('background-attachment', 'fixed');
	@endif
	// var clipboard = new Clipboard('.btn-copy', {
	// 			text: function() {
	// 				return document.querySelector('input[type=hidden]').value;
	// 			}
	// 	});
	//
	// clipboard.on('success', function(e) {
	// 	e.clearSelection();
	// });
	//
	// if (navigator.vendor.indexOf("Apple")==0 && /\sSafari\//.test(navigator.userAgent)) {
	// 	$('.btn-copy').on('click', function() {
	// 		var msg = window.prompt("Copy this link", location.href);
	//
	// 	});
	// }
	function getProfileUrl(e) {
		/* Get the text field */
		// var copyText = document.getElementById('profile-url');
		navigator.clipboard.writeText($("#profile-url").val());
	}
	// $(".modal-list-item .red-checkbox").on("change", function() {
	//
	// 	console.log($("#" + this.id).is(":checked"));
	// });


	$('span.decrement').click(function () {
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

	$('span.increment').click(function () {
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
	
	let lastActiveFilter;
	$('.subscriberFilterModal').on('show.bs.modal', function () {
		lastActiveFilter = $('input[type="radio"]:checked');
		$('.filter-input').trigger('keyup');
	});

	$('.subscriberFilterModal').on('show.bs.modal', function () {
		lastActiveFilter.prop('checked', true);
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