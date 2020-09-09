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
						<div class="fans-form">
							<form method="POST" action="<?php echo e(url('/'.$username.'/settings/save-payment-details')); ?>">
								<?php echo e(csrf_field()); ?>

								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required <?php echo e($errors->has('card_name') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('card_name', trans('Name on Card'))); ?>

											<?php echo e(Form::text('card_name', Auth::user()->name, ['class' => 'form-control', 'placeholder' => trans('Name on Card')])); ?>

											<?php if($errors->has('card_name')): ?>
											<span class="help-block">
												<?php echo e($errors->first('card_name')); ?>

											</span>
											<?php endif; ?>
										</fieldset>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required  <?php echo e($errors->has('cvv') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('card_number', trans('Card Number'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('card_number', Auth::user()->payment()->first()->card_number, array('class' => 'form-control', 'placeholder' => trans('Card Number')))); ?>

											<?php else: ?>
												<?php echo e(Form::text('card_number', '', array('class' => 'form-control', 'placeholder' => trans('Card Number')))); ?>

											<?php endif; ?>
											<?php if($errors->has('card_number')): ?>
												<span class="help-block">
												<?php echo e($errors->first('card_number')); ?>

											</span>
											<?php endif; ?>
										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-3">
										<fieldset class="form-group required <?php echo e($errors->has('cvv') ? ' has-error' : ''); ?> ">
											<?php echo e(Form::label('exp_mm', trans('Expiration Month'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('exp_mm', Auth::user()->payment()->first()->exp_mm, array('class' => 'form-control', 'placeholder' => trans('MM')))); ?>

											<?php else: ?>
												<?php echo e(Form::text('exp_mm', '', array('class' => 'form-control', 'placeholder' => trans('MM')))); ?>

											<?php endif; ?>
											<?php if($errors->has('exp_mm')): ?>
												<span class="help-block">
												<?php echo e($errors->first('exp_mm')); ?>

											</span>
											<?php endif; ?>
										</fieldset>
									</div>
									<div class="col-md-3">
										<fieldset class="form-group required <?php echo e($errors->has('cvv') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('exp_yyyy', trans('Expiration Year'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('exp_yyyy', Auth::user()->payment()->first()->exp_yy, array('class' => 'form-control', 'placeholder' => trans('YYYY')))); ?>

											<?php else: ?>
												<?php echo e(Form::text('exp_yyyy', '', array('class' => 'form-control', 'placeholder' => trans('YYYY')))); ?>

											<?php endif; ?>
											<?php if($errors->has('exp_yyyy')): ?>
												<span class="help-block">
												<?php echo e($errors->first('exp_yyyy')); ?>

											</span>
											<?php endif; ?>
										</fieldset>
									</div>
									<div class="col-md-3">
										<fieldset class="form-group required  <?php echo e($errors->has('cvv') ? ' has-error' : ''); ?>">
											<?php echo e(Form::label('cvv', trans('CVV'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('cvv', Auth::user()->payment()->first()->cvv, array('class' => 'form-control', 'placeholder' => trans('CVV')))); ?>

											<?php else: ?>
												<?php echo e(Form::text('cvv', '', array('class' => 'form-control', 'placeholder' => trans('CVV')))); ?>

											<?php endif; ?>
											<?php if($errors->has('cvv')): ?>
												<span class="help-block">
												<?php echo e($errors->first('cvv')); ?>

											</span>
											<?php endif; ?>
										</fieldset>
									</div>									
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('billing_address', trans('Billing Street Address'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('billing_address', Auth::user()->payment()->first()->billing_address, ['class' => 'form-control', 'placeholder' => trans('Billing Street Address')])); ?>

											<?php else: ?>
												<?php echo e(Form::text('billing_address', '', ['class' => 'form-control', 'placeholder' => trans('Billing Street Address')])); ?>

											<?php endif; ?>
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('billing_city', trans('City'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('billing_city', Auth::user()->payment()->first()->billing_city, ['class' => 'form-control', 'placeholder' => trans('City')])); ?>

											<?php else: ?>
												<?php echo e(Form::text('billing_city', '', ['class' => 'form-control', 'placeholder' => trans('City')])); ?>

											<?php endif; ?>
										</fieldset>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('billing_state', trans('State/Province'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('billing_state', Auth::user()->payment()->first()->billing_state, ['class' => 'form-control', 'placeholder' => trans('State/Province')])); ?>

											<?php else: ?>
												<?php echo e(Form::text('billing_state', '', ['class' => 'form-control', 'placeholder' => trans('State/Province')])); ?>

											<?php endif; ?>
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											<?php echo e(Form::label('billing_zip', trans('Zip/Postal Code'))); ?>

											<?php if(Auth::user()->is_payment_set == true): ?>
												<?php echo e(Form::text('billing_zip', Auth::user()->payment()->first()->billing_zip, ['class' => 'form-control', 'placeholder' => trans('Zip/Postal Code')])); ?>

											<?php else: ?>
												<?php echo e(Form::text('billing_zip', '', ['class' => 'form-control', 'placeholder' => trans('Zip/Postal Code')])); ?>

											<?php endif; ?>
										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-10">
										<fieldset class="form-group <?php echo e($errors->has('cvv') ? ' has-error' : ''); ?>">
											<?php echo e(Form::checkbox('subscribe_content_confirm', 'Yes')); ?>

											<?php echo e(Form::label('subscribe_content_confirm',trans('Yes, I am at least 18 years old and confirm use of my card for transactions.'))); ?>

										</fieldset>
										<?php if($errors->has('subscribe_content_confirm')): ?>
											<span class="help-block">
												<?php echo e($errors->first('subscribe_content_confirm')); ?>

											</span>
										<?php endif; ?>
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
