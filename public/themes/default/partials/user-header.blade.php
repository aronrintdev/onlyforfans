<style>
    .smallscreen-report, a.page-report.report {
        display: block !important;
    }
</style>
<div class="timeline-cover-section">
	<div class="timeline-cover">
		<ul class="list-inline pagelike-links">
			{{--			@if($user_post == true)--}}
			<li class="timeline-cover-status {{ Request::segment(2) == 'posts' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/posts') }}" ><span class="top-list">{{ count($timeline->posts()->where('active', 1)->get()) }} {{ trans('common.posts') }}</span></a></li>
			{{--			@else--}}
			{{--				<li class="timeline-cover-status {{ Request::segment(2) == 'posts' ? 'active' : '' }}"><a href="#"><span class="top-list">{{ count($timeline->posts()->where('active', 1)->get()) }} {{ trans('common.posts') }}</span></a></li>--}}
			{{--			@endif--}}
		<!-- <li class="{{ Request::segment(2) == 'following' ? 'active' : '' }} smallscreen-report"><a href="{{ url($timeline->username.'/following') }}" ><span class="top-list">{{ $following_count }} {{ trans('common.following') }}</span></a></li>
		<li class="{{ Request::segment(2) == 'followers' ? 'active' : '' }} smallscreen-report"><a href="{{ url($timeline->username.'/followers') }}" ><span class="top-list">{{ $followers_count }}  {{ trans('common.followers') }}</span></a></li>-->

			@if(!$user->timeline->albums->isEmpty())
				<li class=""><a href="{{ url($timeline->username.'/albums') }}" > {{ trans('common.photos') }}</span></a></li>
			@endif
			<li class="timeline-cover-status {{ Request::segment(2) == 'followers' ? 'active' : '' }}">
				<a href="{{ url($timeline->username.'/followers') }}" ><span class="top-list">{{ $followers_count }}  {{ trans('common.followers') }}</span>
				</a>
			</li>
			<li class="timeline-cover-status {{ Request::segment(2) == 'followers' ? 'active' : '' }}">
				<a href="#" ><span class="top-list"><span class="liked-post">{{count($liked_post)}}</span> {{ trans('common.likes') }}</span>
				</a>
			</li>
			<li class="timeline-cover-status {{ Request::segment(2) == 'followers' ? 'active' : '' }}">
				<a href="{{ url($timeline->username.'/following') }}" ><span class="top-list">{{ $following_count }}  {{ trans('common.following') }}</span>
				</a>
			</li>
			@if(Auth::user()->username != $timeline->username)
				@if(!$timeline->reports->contains(Auth::user()->id))
					<li class="timeline-cover-status">
						<a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i> {{ trans('common.report') }}
						</a>
					</li>
					<li class="timeline-cover-status hidden">
						<a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>	{{ trans('common.reported') }}
						</a>
					</li>
				@else
					<li class="timeline-cover-status hidden">
						<a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i> {{ trans('common.report') }}
						</a>
					</li>
					<li class="timeline-cover-status">
						<a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>	{{ trans('common.reported') }}
						</a>
					</li>
				@endif
			@endif
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
		<div class="profile-dropdown-menu">
			<ul class="list-inline no-margin">
				<li class="dropdown">
					<a href="#" class="dropdown-togle profile-dropdown-icon" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<svg style="margin-right:15px;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-three-dots-vertical" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                        </svg>
					</a>
					<ul class="dropdown-menu profile-dropdown-menu-content">
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
			<!-- <div class="cover-bottom">
		</div> -->
		<div class="user-timeline-name">
			<a href="{{ url($timeline->username) }}">{{ $timeline->name }}</a><br><a class="user-timeline-username" href="{{ url($timeline->username) }}"><span>@</span>{{ $timeline->username }}</a>
				{!! verifiedBadge($timeline) !!}
		</div>
		</div>
	<div class="timeline-list">
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
{{--						<button type="button" class="close" data-dismiss="modal">&times;</button>--}}
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
<div id="sendTipModal" class="tip-modal modal fade" role="dialog" tabindex='1'>
    <input type="hidden" value="{{$user->id}}" id="user-id">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-header lists-modal" style="display: flex; justify-content: space-between">
                    {{--						<button type="button" class="close" data-dismiss="modal">&times;</button>--}}
                    <h3 class="modal-title lists-modal-title">
                        {{ trans("common.send_tip") }}
                    </h3>
                    @if(!Auth::user()->is_payment_set)
                        <em class="text-danger">Please add Payment card.</em>
                    @endif
                </div>
                <div class="b-stats-row__content">
                    <input type="number" id="etTipAmount" class="form-control etTipAmount" placeholder="$0.00" step="0.1">
                </div>
            </div>
            <div class="modal-footer">
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
</script>
