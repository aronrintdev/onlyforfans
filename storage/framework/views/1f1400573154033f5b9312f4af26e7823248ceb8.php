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
							Add Banking Details
						</h3>
					</div>
					<div class="panel-body nopadding">
						<div class="socialite-form">							
							<form method="POST" action="<?php echo e(url('/'.$username.'/settings/general/')); ?>">
								<?php echo e(csrf_field()); ?>

								
								<div class="row">
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
										<fieldset class="form-group required">
											<?php echo e(Form::label('gender', trans('common.gender'))); ?>

											<?php echo e(Form::select('gender', array('male' => trans('common.male'), 'female' => trans('common.female'), 'other' => trans('common.none')), Auth::user()->gender, array('class' => 'form-control'))); ?>

										</fieldset>
									</div>
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
								</div>

								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('country', trans('common.country'))); ?>

											<?php echo e(Form::text('country', Auth::user()->country, array('class' => 'form-control', 'placeholder' => trans('common.country')))); ?>

										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('address', trans('Street Address'))); ?>

											<?php echo e(Form::text('address', Auth::user()->address, ['class' => 'form-control', 'placeholder' => trans('Street Address')])); ?>

										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('city', trans('City'))); ?>

											<?php echo e(Form::text('city', Auth::user()->city, ['class' => 'form-control', 'placeholder' => trans('City')])); ?>

										</fieldset>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('state', trans('State/Province'))); ?>

											<?php echo e(Form::text('state', Auth::user()->state, ['class' => 'form-control', 'placeholder' => trans('State/Province')])); ?>

										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('zip', trans('Zip/Postal Code'))); ?>

											<?php echo e(Form::text('zip', Auth::user()->zip, ['class' => 'form-control', 'placeholder' => trans('Zip/Postal Code')])); ?>

										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('document', trans('Document Type'))); ?>

											<?php echo e(Form::select('document', array('Select' => 'Select One...', 'Passport' => 'Passport', 'Drivers License' => 'Drivers License', 'ID Card' => 'ID Card'), 'Select')); ?>

										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('photoID', trans('Photo of Your ID'))); ?>

											<?php echo e(Form::file('image')); ?>

										</fieldset>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('photoholdingID', trans('Photo of You Holding ID'))); ?>

											<?php echo e(Form::file('image')); ?>

										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-10">
										<fieldset class="form-group">
											<?php echo e(Form::checkbox('content', 'Yes')); ?>

											<?php echo e(Form::label('content', trans('Yes, my account will contain explicit or adult content'))); ?>

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
										<?php echo e(Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success oauth-link'])); ?>

									</div>
									<div class="clearfix"></div>
								</form>
							</div>
						</div>
					</div>
					<!-- End of first panel -->















































					<!-- End of second panel -->

				</div>
			</div><!-- /row -->
		</div>
	<!-- </div> --><!-- /main-content -->
