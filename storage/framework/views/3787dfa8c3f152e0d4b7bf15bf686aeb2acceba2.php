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
					<h3 class="panel-title">
						<?php echo e(trans('common.wallpaper_settings')); ?>

					</h3>

				</div>
				<div class="panel-body">
					<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
					<br>
					<h3>
						<?php echo e(trans('common.upload_new')); ?>

					</h3>
					<hr>
					<div class="row">
						
						<div class="col-md-4 socialite-form">
							<form method="POST" action="<?php echo e(url('/'.Auth::user()->username.'/settings/wallpaper/')); ?>" files="true" enctype="multipart/form-data" class="form">
								<?php echo e(csrf_field()); ?>

								<div class="row">
									<div class="form-group">
										<input type="file" name="wallpaper" class="" accept="image/jpeg,image/png,image/gif">
									</div>
									<div class="form-group">
										<button type="submit" class="btn btn-primary btn-downloadreport add-wallpapers"> 
											<i class="fa fa-upload" aria-hidden="true"></i>
											<?php echo e(trans('common.upload')); ?>

										</button>
									</div>
								</div>
							</form>

						</div>
						<div class="col-md-4">
							<?php if(Auth::user()->timeline->background_id != NULL): ?>

							<div class="panel panel-default">

								<div class="panel-body nopadding">
									<div class="padding-t5 text-center">
										<?php echo e(trans('common.active_wallpaper')); ?>

									</div>
									<div class="widget-card wallpaper">
										
										<div class="widget-card-bg">	
											<img src="<?php echo url('/wallpaper/'.Auth::user()->timeline->wallpaper->source); ?>" alt="<?php echo e(Auth::user()->timeline->wallpaper->title); ?>">
										</div>

									</div>

									<div class="pull-right activate padding-t5">
										<?php if(Auth::user()->timeline->background_id == Auth::user()->timeline->wallpaper->id): ?>
											<span class="label label-success"><?php echo e(trans('common.active')); ?></span>
										<?php endif; ?>
									</div>
									<div class="pull-left activate" style="padding-left: 11px">
										<?php if(Auth::user()->timeline->background_id == Auth::user()->timeline->wallpaper->id): ?>
										<a href="<?php echo e(url('/'.Auth::user()->username.'/settings/toggle-wallpaper/deactivate/'.Auth::user()->timeline->wallpaper->id)); ?>" class="btn btn-primary"><?php echo e(trans('common.no_wallpaper')); ?></a>
										<?php else: ?>
										<a href="<?php echo e(url('/'.Auth::user()->username.'/settings/toggle-wallpaper/activate/'.Auth::user()->timeline->wallpaper->id)); ?>" class="btn btn-default"><?php echo e(trans('common.activate')); ?></a>
										<?php endif; ?>

									</div>
		
								</div>
							</div><!-- /panel -->

							<?php endif; ?>
						</div>
					</div>
					<br>
					
					<h3>
						<?php echo e(trans('common.select_from_existing')); ?>

					</h3>
					<hr>
					<?php if(count($wallpapers) > 0): ?>
					<ul id="video-thumbnails" class="list-unstyled row">
						<?php $__currentLoopData = $wallpapers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wallpaper): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						
						<li class="col-xs-6 col-sm-4 col-md-4">
							<div class="panel panel-default">
								<div class="panel-body nopadding">
									<div class="widget-card preview wallpaper">
										<div class="widget-card-bg">	
											<img src="<?php echo url('/wallpaper/'.$wallpaper->media->source); ?>" alt="<?php echo e($wallpaper->title); ?>">
										</div>
										<div class="widget-card-project">
											<div class="bridge-text text-center ">
												<a data-sub-html="<h4><?php echo e($wallpaper->title); ?></h4>" href="<?php echo url('/wallpaper/'.$wallpaper->media->source); ?>"  class="btn lightgallery-item btn-default btn-single btn-lightbox btn-sm"><i class="fa fa-search"></i> <?php echo e(trans('common.view_image')); ?></a>

											</div>
										</div>
									</div>
									<div class="pull-right activate padding-t5">
										<?php if(Auth::user()->timeline->background_id == $wallpaper->media->id): ?>
											<span class="label label-success"><?php echo e(trans('common.active')); ?></span>
										<?php endif; ?>
									</div>
									<div class="pull-left activate" style="padding-left: 11px">
										<?php if(Auth::user()->timeline->background_id == $wallpaper->media->id): ?>
										<a href="<?php echo e(url('/'.Auth::user()->username.'/settings/toggle-wallpaper/deactivate/'.$wallpaper->media->id)); ?>" class="btn btn-primary"><?php echo e(trans('common.no_wallpaper')); ?></a>
										<?php else: ?>
										<a href="<?php echo e(url('/'.Auth::user()->username.'/settings/toggle-wallpaper/activate/'.$wallpaper->media->id)); ?>" class="btn btn-default"><?php echo e(trans('common.activate')); ?></a>
										<?php endif; ?>

									</div>
								</div>
							</div><!-- /panel -->

						</li>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</ul>
					<?php else: ?>
						<div class="alert alert-warning">
							<?php echo e(trans('common.no_existing_wallpapers')); ?>

						</div>
					<?php endif; ?>
					
				</div>
				<!-- End of first panel -->

			</div>
		</div><!-- /row -->
	</div>
	<!-- </div> --><!-- /main-content -->
	
	