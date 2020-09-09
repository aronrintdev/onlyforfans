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
						<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
						<div class="panel panel-default">
							<div class="panel-heading no-bg panel-settings">
								<h3 class="panel-title">
									<?php echo e(trans('common.deactivate_account')); ?>

								</h3>
							</div>
							<div class="panel-body no-padding">
								<div class="accout-deactivate">
									<img src="<?php echo e(Theme::asset()->url('images/delete-img.png')); ?>" alt="images">
									<div class="delete-text">
										<?php echo e(trans('messages.confirm_deactivate_question')); ?>

									</div>
								</div>
								<div class="delete-btn">
									<form action="<?php echo e(url(Auth::user()->username.'/deleteme')); ?>" class="user-delete-form">
										<button type="button" class="btn btn-delete-user btn-danger"><?php echo e(trans('messages.yes_deactivate')); ?></button>
									</form>
								</div>
							</div>
						<!--ending first panel-->
						
						
						</div>
					</div>
				</div>
			</div>
		<!-- </div> -->