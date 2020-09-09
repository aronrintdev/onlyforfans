<div class="container">

<div class="row tpadding-20">
  <div class="col-md-6">

    <h2 class="register-heading">Create an Account</h2>
    <div class="panel panel-default">
      <div class="panel-body nopadding">

        <div class="login-bottom">

          <ul class="signup-errors text-danger list-unstyled"></ul>

          <form method="POST" class="signup-form" action="<?php echo e(url('/register')); ?>">
            <?php echo e(csrf_field()); ?>





















            <div class="row">
              <div class="col-md-12">
                <fieldset class="form-group required <?php echo e($errors->has('email') ? ' has-error' : ''); ?>">
                  <?php echo e(Form::label('email', trans('auth.email_address'))); ?> 
                  <?php echo e(Form::text('email', NULL, ['class' => 'form-control', 'id' => 'email', 'placeholder'=> trans('auth.email_address')])); ?>

                  <?php if($errors->has('email')): ?>
                  <span class="help-block">
                    <?php echo e($errors->first('email')); ?>

                  </span>
                  <?php endif; ?>
                </fieldset>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <fieldset class="form-group required <?php echo e($errors->has('name') ? ' has-error' : ''); ?>">
                  <?php echo e(Form::label('name', trans('auth.name'))); ?> 
                  <?php echo e(Form::text('name', NULL, ['class' => 'form-control', 'id' => 'name', 'placeholder'=> trans('auth.name')])); ?>

                  <?php if($errors->has('name')): ?>
                  <span class="help-block">
                    <?php echo e($errors->first('name')); ?>

                  </span>
                  <?php endif; ?>
                </fieldset>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <fieldset class="form-group required <?php echo e($errors->has('gender') ? ' has-error' : ''); ?>">
                  <?php echo e(Form::label('gender', trans('common.gender'))); ?>

                  <?php echo e(Form::select('gender', array('female' => 'Female', 'male' => 'Male', 'other' => 'None'), null, ['placeholder' => trans('auth.select_gender'), 'class' => 'form-control'])); ?>

                  <?php if($errors->has('gender')): ?>
                  <span class="help-block">
                    <?php echo e($errors->first('gender')); ?>

                  </span>
                  <?php endif; ?>
                </fieldset>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <fieldset class="form-group required <?php echo e($errors->has('username') ? ' has-error' : ''); ?>">
                  <?php echo e(Form::label('username', trans('common.username'))); ?> 
                  <?php echo e(Form::text('username', NULL, ['class' => 'form-control', 'id' => 'username', 'placeholder'=> trans('common.username')])); ?>

                  <?php if($errors->has('username')): ?>
                  <span class="help-block">
                    <?php echo e($errors->first('username')); ?>

                  </span>
                  <?php endif; ?>
                <small class="text-muted"><a href="<?php echo e(url('/')); ?>"><?php echo e(url('/')); ?>/username</a></small>
                </fieldset>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-12">
                <fieldset class="form-group required <?php echo e($errors->has('password') ? ' has-error' : ''); ?>">
                  <?php echo e(Form::label('password', trans('auth.password'))); ?> 
                  <?php echo e(Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')])); ?>

                  <?php if($errors->has('password')): ?>
                  <span class="help-block">
                    <?php echo e($errors->first('password')); ?>

                  </span>
                  <?php endif; ?>
                </fieldset>
              </div>
            </div>

            <div class="row">
              <?php if(Setting::get('birthday') == "on"): ?>
              <div class="col-md-6">
                <fieldset class="form-group">
                  <?php echo e(Form::label('birthday', trans('common.birthday'))); ?><i class="optional">(optional)</i>
                  <div class="input-group date datepicker">
                    <span class="input-group-addon addon-left calendar-addon">
                      <span class="fa fa-calendar"></span>
                    </span>
                    <?php echo e(Form::text('birthday', NULL, ['class' => 'form-control', 'id' => 'datepicker1'])); ?>

                    <span class="input-group-addon addon-right angle-addon">
                      <span class="fa fa-angle-down"></span>
                    </span>
                  </div>
                </fieldset>
              </div>
              <?php endif; ?>

              <?php if(Setting::get('city') == "on"): ?>
              <div class="col-md-6">
                <fieldset class="form-group">
                  <?php echo e(Form::label('city', trans('common.current_city'))); ?><i class="optional">(optional)</i>
                  <?php echo e(Form::text('city', NULL, ['class' => 'form-control', 'placeholder' => trans('common.current_city')])); ?>

                </fieldset>
              </div>
              <?php endif; ?>   
            </div>

            <div class="row">
              <?php if(Setting::get('captcha') == "on"): ?>
              <div class="col-md-12">
                <fieldset class="form-group<?php echo e($errors->has('captcha_error') ? ' has-error' : ''); ?>">
                  <?php echo app('captcha')->display(); ?>

                  <?php if($errors->has('captcha_error')): ?>
                  <span class="help-block">
                    <?php echo e($errors->first('captcha_error')); ?>

                  </span>
                  <?php endif; ?>
                </fieldset>
              </div>    
              <?php endif; ?>    
            </div>

            <?php echo e(Form::button(trans('auth.signup_to_dashboard'), ['type' => 'submit','class' => 'btn btn-success btn-submit'])); ?>

          </form>
        </div>  
        <?php if((env('GOOGLE_CLIENT_ID') != NULL && env('GOOGLE_CLIENT_SECRET') != NULL) ||
          (env('TWITTER_CLIENT_ID') != NULL && env('TWITTER_CLIENT_SECRET') != NULL) ||
          (env('FACEBOOK_CLIENT_ID') != NULL && env('FACEBOOK_CLIENT_SECRET') != NULL) ||
          (env('LINKEDIN_CLIENT_ID') != NULL && env('LINKEDIN_CLIENT_SECRET') != NULL) ): ?>
          <div class="divider-login">
            <div class="divider-text"> <?php echo e(trans('auth.login_via_social_networks')); ?></div>
          </div>
          <?php endif; ?>
          <ul class="list-inline social-connect">
            <?php if(env('GOOGLE_CLIENT_ID') != NULL && env('GOOGLE_CLIENT_SECRET') != NULL): ?>
            <li><a href="<?php echo e(url('google')); ?>" class="btn btn-social google-plus"><span class="social-circle"><i class="fa fa-google-plus" aria-hidden="true"></i></span></a></li> 
            <?php endif; ?>

            <?php if(env('TWITTER_CLIENT_ID') != NULL && env('TWITTER_CLIENT_SECRET') != NULL): ?>
            <li><a href="<?php echo e(url('twitter')); ?>" class="btn btn-social tw"><span class="social-circle"><i class="fa fa-twitter" aria-hidden="true"></i></span></a></li>
            <?php endif; ?>

            <?php if(env('FACEBOOK_CLIENT_ID') != NULL && env('FACEBOOK_CLIENT_SECRET') != NULL): ?>
            <li><a href="<?php echo e(url('facebook')); ?>" class="btn btn-social fb"><span class="social-circle"><i class="fa fa-facebook" aria-hidden="true"></i></span></a></li>
            <?php endif; ?>

            <?php if(env('LINKEDIN_CLIENT_ID') != NULL && env('LINKEDIN_CLIENT_SECRET') != NULL): ?> 
            <li><a href="<?php echo e(url('linkedin')); ?>" class="btn btn-social linkedin"><span class="social-circle"><i class="fa fa-linkedin" aria-hidden="true"></i></span></a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div><!-- /panel -->
    </div>
    
  <div class="col-md-6">

    <h2 class="register-heading">Log In</h2>
    <div class="panel panel-default">
      <div class="panel-body nopadding">
        <div class="login-bottom">
            
			<form method="POST" class="signin-form" action="">
				<?php echo e(csrf_field()); ?>

			    <div class="row">
                    <div class="col-md-12">
        				<fieldset class="form-group required <?php echo e($errors->has('email') ? ' has-error' : ''); ?>">
        					<?php echo e(Form::label('email', trans('auth.email_address'))); ?>

        					<?php echo e(Form::text('email', NULL, ['class' => 'form-control', 'id' => 'email', 'placeholder'=> trans('auth.enter_email_or_username')])); ?>

        				</fieldset>
        				
        				<fieldset class="form-group<?php echo e($errors->has('password') ? ' has-error' : ''); ?>">
        					<?php echo e(Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')])); ?>

        					<a href="<?php echo e(url('/password/reset')); ?>" class="forgot-password">Forgot password?</a>
        				</fieldset>
        
        				<?php echo e(Form::button( trans('common.signin') , ['type' => 'submit','class' => 'btn btn-success btn-submit'])); ?>

        		    </div>
                </div>
			</form>
        <!-- <img class="" style="margin: 50px 0 0 50px;" src="<?php echo e(Theme::asset()->url('images/iphone-mockups.png')); ?>" alt="<?php echo e(Setting::get('site_name')); ?>" title="<?php echo e(Setting::get('site_name')); ?>"> -->
        </div>
      </div>
    </div>
    
  </div><!-- /row -->
</div><!-- /container -->
<?php echo Theme::asset()->container('footer')->usePath()->add('app', 'js/app.js'); ?>