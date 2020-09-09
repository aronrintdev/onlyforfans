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
							Add Payment Method
						</h3>
					</div>
					<div class="panel-body nopadding">
						<div class="socialite-form">							
							<form method="POST" action="<?php echo e(url('/'.$username.'/settings/general/')); ?>">
								<?php echo e(csrf_field()); ?>

								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required <?php echo e($errors->has('name') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('name', trans('Name on Card'))); ?>

											<?php echo e(Form::text('name', Auth::user()->name, ['class' => 'form-control', 'placeholder' => trans('Name on Card')])); ?>

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
										<fieldset class="form-group">
											<?php echo e(Form::label('cardnumber', trans('Card Number'))); ?>

											<?php echo e(Form::text('cardnumber', Auth::user()->cardnumber, array('class' => 'form-control', 'placeholder' => trans('Card Number')))); ?>

										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-3">
										<fieldset class="form-group">
											<?php echo e(Form::label('expirationmonth', trans('Expiration Month'))); ?>

											<?php echo e(Form::text('expirationmonth', Auth::user()->expirationmonth, array('class' => 'form-control', 'placeholder' => trans('MM')))); ?>

										</fieldset>
									</div>
									<div class="col-md-3">
										<fieldset class="form-group">
											<?php echo e(Form::label('expirationyear', trans('Expiration Year'))); ?>

											<?php echo e(Form::text('expirationyear', Auth::user()->expirationyear, array('class' => 'form-control', 'placeholder' => trans('YYYY')))); ?>

										</fieldset>
									</div>
									<div class="col-md-3">
										<fieldset class="form-group">
											<?php echo e(Form::label('cvv', trans('CVV'))); ?>

											<?php echo e(Form::text('cvv', Auth::user()->cvv, array('class' => 'form-control', 'placeholder' => trans('CVV')))); ?>

										</fieldset>
									</div>									
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('address', trans('Billing Street Address'))); ?>

											<?php echo e(Form::text('address', Auth::user()->address, ['class' => 'form-control', 'placeholder' => trans('Billing Street Address')])); ?>

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
									<div class="col-md-10">
										<fieldset class="form-group">
											<?php echo e(Form::checkbox('content', 'Yes')); ?>

											<?php echo e(Form::label('content', trans('Yes, I am at least 18 years old and confirm use of my card for transactions.'))); ?>

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
							</div>
						</div>
					</div>
					<!-- End of first panel -->















































					<!-- End of second panel -->

				</div>
			</div><!-- /row -->
		</div>
	<!-- </div> --><!-- /main-content -->
