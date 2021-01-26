<nav class="navbar fans navbar-default no-bg guest-nav">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand fans" href="{{ url('/') }}" style="padding-top:20px;">
			    {{ Setting::get('site_title') }}
                {{--<img class="fans-logo" src="{{ asset('images/logo.png') }}" alt="{{ Setting::get('site_name') }}" title="{{ Setting::get('site_name') }}">--}}
			</a>
		</div>
		@if (Auth::guest())
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
{{--			<form method="POST" class=" navbar-form navbar-right" action="">--}}
{{--				{{ csrf_field() }}--}}
{{--				<fieldset class="form-group mail-form {{ $errors->has('email') ? ' has-error' : '' }}">--}}
{{--					{{ Form::text('email', NULL, ['class' => 'form-control', 'id' => 'email', 'placeholder'=> trans('auth.enter_email_or_username')]) }}--}}
{{--				</fieldset>--}}
{{--				<fieldset class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">--}}
{{--					{{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')]) }}--}}
{{--					<a href="{{ url('/password/reset') }}" class="forgot-password">Forgot your password</a>--}}
{{--				</fieldset>--}}
{{--				{{ Form::button( trans('common.signin') , ['type' => 'submit','class' => 'btn btn-success btn-submit']) }}--}}
			</form>
		</div>
		@endif
	</div>
</nav>