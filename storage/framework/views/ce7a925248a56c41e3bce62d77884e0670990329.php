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

					<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
					
					<div class="panel-body nopadding">
						<div class="fans-form">
							<form method="POST" action="<?php echo e(url('/'.$username.'/settings/profile/')); ?>">
								<?php echo e(csrf_field()); ?>


								<h3>
									Edit <?php echo e(trans('common.personal')); ?>

								</h3>
								<hr>

    								<fieldset class="form-group">
    									<?php echo e(Form::label('about', trans('common.about'))); ?>

    									<?php echo e(Form::textarea('about', Auth::user()->timeline->about, ['class' => 'form-control', 'placeholder' => trans('messages.about_user_placeholder')])); ?>

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
												<?php echo e(Form::label('wishlist', trans('common.designation'))); ?>

												<?php echo e(Form::text('wishlist', Auth::user()->wishlist, ['class' => 'form-control', 'placeholder' => trans('common.your_qualification')])); ?>

											</fieldset>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">

											<fieldset class="form-group">
												<?php echo e(Form::label('website', trans('common.hobbies'))); ?>

												<?php echo e(Form::text('website', Auth::user()->website, ['class' => 'add_selectize', 'placeholder' => trans('common.mention_your_hobbies')])); ?>

											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group">
												<?php echo e(Form::label('instagram', trans('common.interests'))); ?>

												<?php echo e(Form::text('instagram', Auth::user()->instagram, ['class' => 'add_selectize', 'placeholder' => trans('common.add_your_interests')])); ?>

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
							</div><!-- /fans-form -->
						</div>
					</div>
					<!-- End of first panel -->















































					<!-- End of second panel -->

				</div>
			</div><!-- /row -->
		</div>
	<!-- </div> --><!-- /main-content -->
