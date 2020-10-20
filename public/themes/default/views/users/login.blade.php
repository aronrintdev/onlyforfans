<div class="login-block">
    <div class="panel panel-default">
        <div class="panel-body nopadding">
            <div class="login-head">
                {{ trans('auth.login_welcome_heading') }}
                <div class="login-text" style="font-size:16px; text-align:center;">{{ trans('auth.dont_have_an_account_yet') }}<a href="{{ url('/register') }}"> {{ trans('auth.get_started') }}</a></div>
                <div class="header-circle login-progress hidden"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
            </div>
            <div class="login-bottom">
                <div class="login-errors text-danger"></div>
                @if (Config::get('app.env') == 'demo')
                    <div class="alert alert-success">
                        username : <code>bootstrapguru</code> &nbsp;&nbsp;&nbsp;   password : <code>fans</code>
                    </div>
                @endif
                @if(Request::get('echk') == "on")
                    <div class="alert alert-info fade in" id="emailalert">
                        <a href="#" class="close" data-dismiss="alert">&times;</a>
                        <strong>{{ trans('auth.email_verify') }}</strong> 
                    </div>
                @endif
                @if(session()->has('login_notice'))
                <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    {{ session()->get('login_notice') }}
                </div>
                @endif 
                <form method="POST" class="login-form" action="{{ url('/main-login') }}">
                    {{ csrf_field() }}
                    <fieldset class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        {{ Form::text('email', NULL, ['class' => 'form-control', 'id' => 'email', 'placeholder'=> trans('auth.enter_email_or_username')]) }}
                    </fieldset>
                    <fieldset class="form-group{{ $errors->has('password') ? ' has-error' : '' }}" style="margin-bottom:0px;">
                        {{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')]) }}
                    </fieldset>
                    <div class="pull-right" style="margin-bottom:18px;">
                        <a class="forgot-password-link" href="{{ url('/password/reset') }}">{{ trans('auth.forgot_password').'?' }}</a>
                    </div>
                    {{ Form::button( trans('common.signin') , ['type' => 'submit','class' => 'btn btn-success btn-submit']) }}
                </form>
            </div>
            <div class="divider-login">
                <div class="divider-text">{{ trans('auth.login_via_social_networks') }}</div>
            </div>
            <ul class="list-unstyled social-connect">
                <li style="margin-bottom: 10px"><a href="{{ url('twitter') }}" class="btn btn-social tw" style="text-transform:none;"><span style="margin-top:8px; margin-right:10px; display:inline-block;"><i class="fa fa-twitter" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign in with Twitter</span></a></li>
                <li style="margin-bottom: 10px"><a href="{{ url('google') }}" class="btn btn-social google-plus" style="text-transform:none;"><span style="margin-top:8px; margin-right:10px; display:inline-block;"><i class="fa fa-google" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign in with Google</span></a></li>
                <li style="margin-bottom: 10px"><a href="{{ url('facebook') }}" class="btn btn-social fb" style="text-transform:none;"><span style="margin-top:10px; margin-right:8px; display:inline-block;"><i class="fa fa-facebook" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign in with Facebook</span></a></li>
                <li><a href="{{ url('apple') }}" class="btn btn-social apple" style="text-transform:none;"><span style="margin-top:10px; margin-right:8px; display:inline-block;"><i class="fa fa-apple" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign in with Apple</span></a></li>                
            </ul>
        </div>
    </div>
</div>
