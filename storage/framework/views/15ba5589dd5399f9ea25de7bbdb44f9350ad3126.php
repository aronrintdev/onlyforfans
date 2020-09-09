<!-- <div class="main-content"> -->
	<div class="container">
		<div class="row">
			<div class="col-md-8">
				<div class="panel panel-default">
					<div class="panel-heading no-bg panel-settings">
						<h3 class="panel-title"><?php echo e(trans('common.create_page')); ?></h3>
					</div>

					<div class="panel-body nopadding">  
						<div class="socialite-form">
							<?php if(isset($message)): ?>
							<div class="alert alert-success">                                       
								<?php echo e($message); ?>                                    
							</div>
							<?php endif; ?>                          
							<form class="margin-right" method="POST" action="<?php echo e(url('/'.$username.'/create-page/')); ?>">
								<?php echo e(csrf_field()); ?>


								<fieldset class="form-group required <?php echo e($errors->has('category') ? ' has-error' : ''); ?>">
									<?php echo e(Form::label('category', trans('common.category'), ['class' => 'control-label'])); ?>                            

									<?php echo e(Form::select('category', array('' => trans('common.select_category'))+ $category_options, '', array('class' => 'form-control'))); ?>

									<?php if($errors->has('category')): ?>
									<span class="help-block">
										<?php echo e($errors->first('category')); ?>

									</span>
									<?php endif; ?>

								</fieldset>
								
								<fieldset class="form-group required <?php echo e($errors->has('name') ? ' has-error' : ''); ?>">
									<?php echo e(Form::label('name', trans('auth.name'), ['class' => 'control-label'])); ?>

									<?php echo e(Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('common.name_of_your_page')])); ?>

									<?php if($errors->has('name')): ?>
									<span class="help-block">
										<?php echo e($errors->first('name')); ?>

									</span>
									<?php endif; ?>
								</fieldset> 

								<fieldset class="form-group required <?php echo e($errors->has('username') ? ' has-error' : ''); ?>">
									<?php echo e(Form::label('username', trans('common.username'), ['class' => 'control-label'])); ?>									
									<?php echo e(Form::text('username', old('username'), ['class' => 'form-control','maxlength' => '26', 'placeholder' => trans('common.username')])); ?>

									<?php if($errors->has('username')): ?>
									<span class="help-block">
										<?php echo e($errors->first('username')); ?>

									</span>
									<?php endif; ?>
								</fieldset>

								<fieldset class="form-group">
									<?php echo e(Form::label('about', trans('common.about'), ['class' => 'control-label'])); ?>

									<?php echo e(Form::textarea('about', old('about'), ['class' => 'form-control', 'placeholder' => trans('messages.create_page_placeholder'), 'rows' => '4', 'cols' => '20'])); ?>

								</fieldset>

								<fieldset class="form-group">
									<div class="pull-right">
										<?php echo e(Form::submit(trans('common.create_page'), ['class' => 'btn btn-success'])); ?>

									</div>
								</fieldset>
								
							</form>
						</div>
					</div><!-- /panel-body -->
				</div>
			</div><!-- /col-md-8 -->

			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-heading no-bg panel-settings">
						<h3 class="panel-title"><?php echo e(trans('common.about').' '.trans('common.pages')); ?></h3>
					</div>
					<div class="panel-body right-panel">
						<div class="privacy-question">
							<ul class="list-group right-list-group">
								<li href="#" class="list-group-item">
									<div class="holder">
										<div class="about-page">
											<?php echo e(Form::label('about_page_heading1', trans('messages.about_page_heading1'), ['class' => 'right-side-label'])); ?>

											</div>
										<div class="page-description">
											<?php echo e(trans('messages.about_page_content1')); ?>

										</div>
									</div>
								</li>
								<li href="#" class="list-group-item">
									<div class="holder">
										<div class="about-page">
											<?php echo e(Form::label('about_page_heading2', trans('messages.about_page_heading2'), ['class' => 'right-side-label'])); ?>

											</div>
										<div class="page-description">
											<?php echo e(trans('messages.about_page_content2')); ?>

										</div>
									</div>
								</li>
							</ul><!-- /list-group -->
						</div>
					</div><!-- /panel-body -->
				</div>
				
				<?php if(Setting::get('createpage_ad') != NULL): ?>
				<div id="link_other" class="page-image">
					<?php echo htmlspecialchars_decode(Setting::get('createpage_ad')); ?>

				</div>
				<?php endif; ?>
			</div><!-- /col-md-4 -->
		</div>
	</div><!-- /container -->
<!-- </div> -->