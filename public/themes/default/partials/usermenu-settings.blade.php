<!-- <div class="panel panel-default">
	<div class="panel-body nopadding">
		<div class="mini-profile">
			<div class="background">
		        <div class="widget-bg">
		            <img src=" @if(Auth::user()->cover) {{ url('user/cover/'.Auth::user()->cover) }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}">
		        </div>
				<div class="avatar-img">
					<img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}">
				</div>
			</div>
		    <div class="avatar-profile">
		        <div class="avatar-details">
		            <h2 class="avatar-name"><a href="{{ url(Auth::user()->username) }}">{{ Auth::user()->name }}</h2></a>
		            <h4 class="avatar-mail">
		            	<a href="{{ url(Auth::user()->username) }}" >
		            		{{ '@'.Auth::user()->username }}
		            	</a>
		            </h4>
		        </div>
		    </div>
		</div>
	</div>
</div>
-->
<div class="panel-default list-group list-group-navigation fans-group">
	<a href="{{ url('/'.Auth::user()->username.'/settings/general') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'general' ? 'active' : '' }}">
			<i class="fa fa-user"></i>
		</div>
		<div class="list-text">
			{{ trans('common.general_settings') }}
			<div class="text-muted">
				{{ trans('messages.menu_message_general') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
	<a href="{{ url('/'.Auth::user()->username.'/settings/profile') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'profile' ? 'active' : '' }}">
			<i class="fa fa-pencil-square"></i>
		</div>
		<div class="list-text">
			{{ trans('common.edit_profile') }}
			<div class="text-muted">
				{{ trans('messages.menu_message_profile') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
	<a href="{{ url('/'.Auth::user()->username.'/settings/privacy') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'privacy' ? 'active' : '' }}">
			<i class="fa fa-user-secret"></i>
		</div>
		<div class="list-text">
			{{ trans('common.privacy_settings') }}
			<div class="text-muted">
				{{ trans('messages.menu_message_privacy') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
	<a href="{{ url('/'.Auth::user()->username.'/settings/security') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'security' ? 'active' : '' }}">
			<i class="fa fa-lock"></i>
		</div>
		<div class="list-text">
			{{ trans('common.security_settings') }}
			<div class="text-muted">
				{{ trans('messages.menu_message_security') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
	<a href="{{ url('/'.Auth::user()->username.'/settings/addbank') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'addbank' ? 'active' : '' }}">
			<i class="fa fa-university"></i>
		</div>
		<div class="list-text">
			{{ trans('common.add_bank') }}
			<div class="text-muted">
			{{ trans('messages.menu_message_add_bank') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
	<a href="{{ url('/'.Auth::user()->username.'/settings/addpayment') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'addpayment' ? 'active' : '' }}">
			<i class="fa fa-credit-card"></i>
		</div>
		<div class="list-text">
			{{ trans('common.add_payment') }}
			<div class="text-muted">
			{{ trans('messages.menu_message_add_payment') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
{{--	<a href="{{ url('/'.Auth::user()->username.'/settings/wallpaper') }}" class="list-group-item">--}}
{{--		<div class="list-icon fans-icon {{ Request::segment(3) == 'wallpaper' ? 'active' : '' }}">--}}
{{--			<i class="fa fa-image"></i>--}}
{{--		</div>--}}
{{--		<div class="list-text">--}}
{{--			{{ trans('common.wallpaper_settings') }}--}}
{{--			<div class="text-muted">--}}
{{--				{{ trans('messages.menu_message_wallpaper') }}--}}
{{--			</div>--}}
{{--		</div>--}}
{{--		<div class="clearfix"></div>--}}
{{--	</a>--}}
	<a href="{{ url('/'.Auth::user()->username.'/settings/login_sessions') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'login_sessions' ? 'active' : '' }}">
			<i class="fa fa-user-plus"></i>
		</div>
		<div class="list-text">
			{{ trans('common.login_session') }}
			<div class="text-muted">
				{{ trans('messages.menu_message_login_session') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>

	<a href="{{ url('/'.Auth::user()->username.'/settings/affliates') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'affliates' ? 'active' : '' }}">
			<i class="fa fa-user-plus"></i>
		</div>
		<div class="list-text">
			{{ trans('common.my_affiliates') }}
			<div class="text-muted">
				{{ trans('messages.menu_message_affiliates') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
	<a href="{{ url('/'.Auth::user()->username.'/settings/deactivate') }}" class="list-group-item">
		<div class="list-icon fans-icon {{ Request::segment(3) == 'deactive' ? 'active' : '' }}">
			<i class="fa fa-trash"></i>
		</div>
		<div class="list-text">
			{{ trans('common.deactivate_account') }}
			<div class="text-muted">
				{{ trans('messages.menu_message_deactivate') }}
			</div>
		</div>
		<div class="clearfix"></div>
	</a>
</div>
