<div class="container">

<div class="row tpadding-20">

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

                        <a href="/register" class="btn btn-success" style="margin-top: 10px"><?php echo e(trans('auth.signup_to_dashboard')); ?></a>
        		    </div>

                </div>
			</form>
        <!-- <img class="" style="margin: 50px 0 0 50px;" src="<?php echo e(Theme::asset()->url('images/iphone-mockups.png')); ?>" alt="<?php echo e(Setting::get('site_name')); ?>" title="<?php echo e(Setting::get('site_name')); ?>"> -->
        </div>
          <?php if(isset($error_msg)): ?>
              <span class="help-block" style="color: red; width: auto"><?php echo e($error_msg); ?></span>
          <?php endif; ?>
      </div>
    </div>
    
  </div><!-- /row -->
</div><!-- /container -->
<?php echo Theme::asset()->container('footer')->usePath()->add('app', 'js/app.js'); ?>