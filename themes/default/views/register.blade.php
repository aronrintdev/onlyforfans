<style>
    .register-form-row {
        height: auto;
    }
    
    form .list-unstyled {
        margin: 0;
    }
    
    .form-wrapper {
        margin: auto;
    }
    
    .d-flex {
        display: flex;
    }
</style>
<!-- %VIEW themes/default/views/register.blade.php -->
<div class="container">

<div class="row tpadding-20 d-flex">
  <div class="col-md-6 col-sm-12 form-wrapper">
    <div class="panel panel-default">
      <div class="panel-body nopadding">
        <div class="login-head">{{ trans('common.create_account') }}
        <div class="login-text" style="font-size:16px; text-align:center;">Already have an account? <a href="{{ url('/') }}">Sign in</a></div>
        </div>
        
        <div class="login-bottom">

          <ul class="signup-errors text-danger list-unstyled"></ul>

          <form method="POST" class="signup-form" action="{{ url('/register') }}">
            {{ csrf_field() }}

                  @if( isset($invite_token) )
                  {{ Form::hidden('invite_token', $invite_token) }}
                  @endif

        <div class="row" hidden>
            <div class="col-md-6">
                <fieldset class="form-group{{ $errors->has('affiliate') ? ' has-error' : '' }}">
                  {{ Form::label('affiliate', trans('auth.affiliate_code')) }}<i class="optional">(optional)</i>
                  @if(isset($_GET['affiliate']))
                  {{ Form::text('affiliate', $_GET['affiliate'], ['class' => 'form-control', 'id' => 'affiliate', 'disabled' =>'disabled']) }}
                  {{ Form::hidden('affiliate', $_GET['affiliate']) }}
                  @else
                  {{ Form::text('affiliate', NULL, ['class' => 'form-control', 'id' => 'affiliate', 'placeholder'=> trans('auth.affiliate_code')]) }}
                  @endif
                  @if ($errors->has('affiliate'))
                  <span class="help-block">
                    {{ $errors->first('affiliate') }}
                  </span>
                  @endif
                </fieldset>
            </div>
        </div>

            <div class="row">
              <div class="col-md-12 register-form-row">
                <fieldset class="form-group required {{ $errors->has('email') ? ' has-error' : '' }}">
                  {{ Form::text('email', NULL, ['class' => 'form-control', 'id' => 'email', 'placeholder'=> trans('auth.email_address')]) }}
                  <ul class="signup-email-error text-danger list-unstyled">
                      @if ($errors->has('email'))
                          <span class="help-block">
                    {{ $errors->first('email') }}
                  </span>
                      @endif
                  </ul>
                </fieldset>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12 register-form-row">
                <fieldset class="form-group required {{ $errors->has('name') ? ' has-error' : '' }}">
                  {{ Form::text('name', NULL, ['class' => 'form-control', 'id' => 'name', 'placeholder'=> trans('auth.name')]) }}
                  <ul class="signup-name-error text-danger list-unstyled">
                  </ul>
                </fieldset>
              </div>
            </div>

{{--            <div class="row">--}}
{{--              <div class="col-md-12">--}}
{{--                <fieldset class="form-group required {{ $errors->has('gender') ? ' has-error' : '' }}">--}}
{{--                  {{ Form::label('gender', trans('common.gender')) }}--}}
{{--                  {{ Form::select('gender', array('female' => 'Female', 'male' => 'Male', 'other' => 'None'), null, ['placeholder' => trans('auth.select_gender'), 'class' => 'form-control']) }}--}}
{{--                  @if ($errors->has('gender'))--}}
{{--                  <span class="help-block">--}}
{{--                    {{ $errors->first('gender') }}--}}
{{--                  </span>--}}
{{--                  @endif--}}
{{--                </fieldset>--}}
{{--              </div>--}}
{{--            </div>--}}

            <div class="row">
              <div class="col-md-12 register-form-row">
                <fieldset class="form-group required {{ $errors->has('username') ? ' has-error' : '' }}">
                  {{ Form::text('username', NULL, ['class' => 'form-control', 'id' => 'username', 'placeholder'=> trans('common.username')]) }}
                  <ul class="signup-username-error text-danger list-unstyled">
                  </ul>
                <!-- <small class="text-muted"><a href="{{ url('/') }}">{{ url('/') }}/username</a></small> -->
                </fieldset>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-12 register-form-row">
                <fieldset class="form-group required {{ $errors->has('password') ? ' has-error' : '' }}">
                  {{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')]) }}
                  <ul class="signup-password-error text-danger list-unstyled">
                  </ul>
                </fieldset>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-12" class="register-form-row" style="margin-bottom:20px;">
                  <input type="checkbox" required name="tos" id="tos"><label for="tos" style="cursor:pointer;">&nbsp;I agree to the <a href="#" data-toggle="modal" data-target="#termsConditionsModal">Terms of Service</a>.</label>
                  <ul class="signup-tos-error text-danger list-unstyled">
                  </ul>
              </div>
            </div>

              <div class="modal fade" id="termsConditionsModal" role="dialog">
                  <div class="modal-dialog">

                      <!-- Modal content-->
                      <div class="modal-content">
                          <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title">Terms & Conditions</h4>
                          </div>
                          <div class="modal-body">
                              <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                              </p>
                              <p>
                                  It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>
                          </div>
                          <div class="modal-footer">
                              <label class="btn btn-default agree-btn" for="tos">I Agree</label>
                          </div>
                      </div>

                  </div>
              </div>

            <div class="row">
{{--              @if(Setting::get('birthday') == "on")--}}
{{--              <div class="col-md-6">--}}
{{--                <fieldset class="form-group">--}}
{{--                  {{ Form::label('birthday', trans('common.birthday')) }}<i class="optional">(optional)</i>--}}
{{--                  <div class="input-group date datepicker">--}}
{{--                    <span class="input-group-addon addon-left calendar-addon">--}}
{{--                      <span class="fa fa-calendar"></span>--}}
{{--                    </span>--}}
{{--                    {{ Form::text('birthday', NULL, ['class' => 'form-control', 'id' => 'datepicker1']) }}--}}
{{--                    <span class="input-group-addon addon-right angle-addon">--}}
{{--                      <span class="fa fa-angle-down"></span>--}}
{{--                    </span>--}}
{{--                  </div>--}}
{{--                </fieldset>--}}
{{--              </div>--}}
{{--              @endif--}}

{{--              @if(Setting::get('city') == "on")--}}
{{--              <div class="col-md-6">--}}
{{--                <fieldset class="form-group">--}}
{{--                  {{ Form::label('city', trans('common.current_city')) }}<i class="optional">(optional)</i>--}}
{{--                  {{ Form::text('city', NULL, ['class' => 'form-control', 'placeholder' => trans('common.current_city')]) }}--}}
{{--                </fieldset>--}}
{{--              </div>--}}
{{--              @endif   --}}
            </div>

            <div class="row">
              @if(Setting::get('captcha') == "on")
              <div class="col-md-12">
                <fieldset class="form-group{{ $errors->has('captcha_error') ? ' has-error' : '' }}">
                  {!! app('captcha')->display() !!}
                  @if ($errors->has('captcha_error'))
                  <span class="help-block">
                    {{ $errors->first('captcha_error') }}
                  </span>
                  @endif
                </fieldset>
              </div>    
              @endif    
            </div>

            {{ Form::button(trans('auth.signup_to_dashboard'), ['type' => 'submit','class' => 'btn btn-success btn-submit']) }}
{{--            <a href="/login" class="btn btn-success" style="margin-top: 10px">{{  trans('common.signin') }}</a>--}}
          </form>
        </div>  
        @if(config('services.google.client_id') != NULL && config('services.google.client_secret') ||
          config('services.twitter.client_id') != NULL && config('services.twitter.client_secret') ||
          config('services.facebook.client_id') != NULL && config('services.facebook.client_secret') ||
          config('services.linkedin.client_id') != NULL && config('services.linkedin.client_secret') )
          <div class="divider-login">
            <div class="divider-text"> {{ trans('auth.login_via_social_networks') }}</div>
          </div>
          @endif
        <ul class="list-unstyled social-connect">
            <li style="margin-bottom: 10px"><a href="{{ url('twitter') }}" class="btn btn-social tw" style="text-transform:none;"><span style="margin-top:8px; margin-right:10px; display:inline-block;"><i class="fa fa-twitter" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign up with Twitter</span></a></li>
            <li style="margin-bottom: 10px"><a href="{{ url('google') }}" class="btn btn-social google-plus" style="text-transform:none;"><span style="margin-top:8px; margin-right:10px; display:inline-block;"><i class="fa fa-google" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign up with Google</span></a></li>
            <li style="margin-bottom: 10px"><a href="{{ url('facebook') }}" class="btn btn-social fb" style="text-transform:none;"><span style="margin-top:10px; margin-right:8px; display:inline-block;"><i class="fa fa-facebook" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign up with Facebook</span></a></li>
            <li><a href="{{ url('apple') }}" class="btn btn-social apple" style="text-transform:none;"><span style="margin-top:10px; margin-right:8px; display:inline-block;"><i class="fa fa-apple" style="color:#fff;" aria-hidden="true"></i></span><span style="color: #fff;">Sign up with Apple</span></a></li>                
        </ul>
        </div>
      </div><!-- /panel -->
    </div>
    
  </div><!-- /row -->
</div><!-- /container -->
<script>
    $('#tos').change(function () {
        $('#termsConditionsModal').modal('hide');
    });

    $('#termsConditionsModal').on('show.bs.modal', function () {
        $('.agree-btn').text('I Agree');
        let isChecked = $('#tos').is(':checked');
        if(isChecked) {
            $('.agree-btn').closest('.modal-footer').addClass('hidden');
        } else {
            $('.agree-btn').closest('.modal-footer').removeClass('hidden');
        }
    });
</script>
<script src="{{ asset('themes/default/assets/js/app.js') }}"></script>
