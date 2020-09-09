<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
	<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
		<h3 class="panel-title">
			<?php echo e(trans('common.themes')); ?>

		</h3>
	</div>
	<div class="panel-body">
		<div class="independent-themes">
			<div class="row">
				<?php $__currentLoopData = $themesInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $theme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<div class="col-md-4">
						<div class="independent-block">
							<div class="theme-image">
								<a href="<?php echo e(url('admin/change-theme/'.$theme->directory)); ?>"><img src="<?php echo e(url($theme->thumbnail)); ?>" alt="images"></a>
								<span class="label label-default selected"><?php echo e(trans('common.selected')); ?></span>
							</div>
							<div class="theme-sale">
								<div class="theme-details pull-left">
									<div class="theme-name"><?php echo e($theme->name); ?></div>
									<div class="theme-author"><?php echo e(trans('common.author')); ?> : <?php echo e($theme->author); ?></div>
								</div>
								<div class="theme-rate pull-right">
									<?php echo e($theme->version); ?>

								</div>
								<div class="clearfix"></div>
							</div>
						</div><!-- /independent-block -->
					</div>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</div><!-- /row -->
		</div>
	</div>
</div>