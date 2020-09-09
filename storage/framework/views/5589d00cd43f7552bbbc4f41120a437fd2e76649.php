<!-- main-section -->
<!-- <div class="main-content"> -->
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="post-filters">
					<?php echo Theme::partial('usermenu-settings'); ?>

				</div>
			</div>
			<div class="col-md-8">
				<div class="panel panel-default">
				
					<div class="panel-heading no-bg panel-settings">
					<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
						<h3 class="panel-title">
							<?php echo e(trans('common.general_settings')); ?>

						</h3>
					</div>
					
					<div class="panel-body nopadding">
						<div class="socialite-form">							
							<form method="POST" action="<?php echo e(url('/'.$username.'/settings/general/')); ?>">
								<?php echo e(csrf_field()); ?>

								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required <?php echo e($errors->has('username') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('username', trans('common.username'))); ?>

											<?php echo e(Form::text('new_username', Auth::user()->username, ['class' => 'form-control', 'placeholder' => trans('common.username')])); ?>

											<?php if($errors->has('username')): ?>
											<span class="help-block">
												<?php echo e($errors->first('username')); ?>

											</span>
											<?php endif; ?>
											<small class="text-muted"><a href="<?php echo e(url('/')); ?>"><?php echo e(url('/')); ?>/username</a></small>
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group required <?php echo e($errors->has('name') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('name', trans('common.fullname'))); ?>

											<?php echo e(Form::text('name', Auth::user()->name, ['class' => 'form-control', 'placeholder' => trans('common.fullname')])); ?>

											<?php if($errors->has('name')): ?>
											<span class="help-block">
												<?php echo e($errors->first('name')); ?>

											</span>
											<?php endif; ?>
										</fieldset>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required <?php echo e($errors->has('email') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('email', trans('auth.email_address'))); ?>

											<?php echo e(Form::email('email', Auth::user()->email, ['class' => 'form-control', 'placeholder' => trans('auth.email_address')])); ?>

											<?php if($errors->has('email')): ?>
											<span class="help-block">
												<?php echo e($errors->first('email')); ?>

											</span>
											<?php endif; ?>
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group required">
											<?php echo e(Form::label('gender', trans('common.gender'))); ?>

											<?php echo e(Form::select('gender', array('male' => trans('common.male'), 'female' => trans('common.female'), 'other' => trans('common.none')), Auth::user()->gender, array('class' => 'form-control'))); ?>

										</fieldset>
									</div>
								</div>

								<h3>
									Edit <?php echo e(trans('common.personal')); ?>

								</h3>
								<hr>
									
    								<fieldset class="form-group">
    									<?php echo e(Form::label('about', trans('common.about'))); ?>

    									<?php echo e(Form::textarea('about', Auth::user()->about, ['class' => 'form-control', 'placeholder' => trans('messages.about_user_placeholder')])); ?>

    								</fieldset>
    								
    								<div class="row">
    									<div class="col-md-6">
    										<fieldset class="form-group">
    											<?php echo e(Form::label('country', trans('common.country'))); ?>

    											<?php echo e(Form::text('country', Auth::user()->country, array('class' => 'form-control', 'placeholder' => trans('common.country')))); ?>

    										</fieldset>
    									</div>
    									<div class="col-md-6">
    										<fieldset class="form-group">
    											<?php echo e(Form::label('city', trans('common.current_city'))); ?>

    											<?php echo e(Form::text('city', Auth::user()->city, ['class' => 'form-control', 'placeholder' => trans('common.current_city')])); ?>

    										</fieldset>
    									</div>
    								</div>
    								
									<div class="row">
										<div class="col-md-6">
											<fieldset class="form-group">
												<?php echo e(Form::label('birthday', trans('common.birthday'))); ?>

												<div class="input-group date datepicker">
													<span class="input-group-addon addon-left calendar-addon">
														<span class="fa fa-calendar"></span>
													</span>
													<?php echo e(Form::text('birthday', Auth::user()->birthday, ['class' => 'form-control', 'id' => 'datepicker1'])); ?>

													<span class="input-group-addon addon-right angle-addon">
														<span class="fa fa-angle-down"></span>
													</span>
												</div>
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group">
												<?php echo e(Form::label('designation', trans('common.designation'))); ?>

												<?php echo e(Form::text('designation', Auth::user()->designation, ['class' => 'form-control', 'placeholder' => trans('common.your_qualification')])); ?>

											</fieldset>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">

											<fieldset class="form-group">
												<?php echo e(Form::label('hobbies', trans('common.hobbies'))); ?>

												<?php echo e(Form::text('hobbies', Auth::user()->hobbies, ['class' => 'add_selectize', 'placeholder' => trans('common.mention_your_hobbies')])); ?>

											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group">
												<?php echo e(Form::label('interests', trans('common.interests'))); ?>

												<?php echo e(Form::text('interests', Auth::user()->interests, ['class' => 'add_selectize', 'placeholder' => trans('common.add_your_interests')])); ?>

											</fieldset>
										</div>
									</div>
									<?php if(Setting::get('custom_option1') != NULL || Setting::get('custom_option2') != NULL): ?>
										<div class="row">
											<?php if(Setting::get('custom_option1') != NULL): ?>
											<div class="col-md-6">
												<fieldset class="form-group">
													<?php echo e(Form::label('custom_option1', Setting::get('custom_option1'))); ?>

													<?php echo e(Form::text('custom_option1', Auth::user()->custom_option1, ['class' => 'form-control'])); ?>

												</fieldset>
											</div>
											<?php endif; ?>

											<?php if(Setting::get('custom_option2') != NULL): ?>
											<div class="col-md-6">
												<fieldset class="form-group">
													<?php echo e(Form::label('custom_option2', Setting::get('custom_option2'))); ?>

													<?php echo e(Form::text('custom_option2', Auth::user()->custom_option2, ['class' => 'form-control'])); ?>

												</fieldset>
											</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php if(Setting::get('custom_option3') != NULL || Setting::get('custom_option4') != NULL): ?>
										<div class="row">
											<?php if(Setting::get('custom_option3') != NULL): ?>
											<div class="col-md-6">
												<fieldset class="form-group">
													<?php echo e(Form::label('custom_option3', Setting::get('custom_option3'))); ?>

													<?php echo e(Form::text('custom_option3', Auth::user()->custom_option3, ['class' => 'form-control'])); ?>

												</fieldset>
											</div>
											<?php endif; ?>

											<?php if(Setting::get('custom_option4') != NULL): ?>
											<div class="col-md-6">
												<fieldset class="form-group">
													<?php echo e(Form::label('custom_option4', Setting::get('custom_option4'))); ?>

													<?php echo e(Form::text('custom_option4', Auth::user()->custom_option4, ['class' => 'form-control'])); ?>

												</fieldset>
											</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>


									<div class="pull-right">
										<?php echo e(Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success'])); ?>

									</div>
									<div class="clearfix"></div>
								</form>
							</div><!-- /Socialite-form -->
						</div>
					</div>
					<!-- End of first panel -->















































					<!-- End of second panel -->

				</div>
			</div><!-- /row -->
		</div>
	<!-- </div> --><!-- /main-content -->
