<!-- main-section -->

<div class="container">
	<div class="row">
		<div class="visible-lg col-lg-2">
			<br>
			<?php echo Theme::partial('home-leftbar',compact('trending_tags')); ?>

		</div>

		<div class="col-md-10">
			<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
			<div class="panel panel-default">
        <div class="panel-heading no-bg user-pages no-paddingbottom navbars">
            <div class="page-heading header-text">
                <?php echo e(trans('common.all_albums')); ?>

            </div>
            <?php if(Auth::user()->id == $timeline->user->id): ?>
            <div class="pull-right">
                <a href="<?php echo e(url('/'.Auth::user()->username.'/album/create')); ?>" class="btn btn-success btn-downloadreport"><?php echo e(trans('common.create_album')); ?></a>
            </div>
            <?php endif; ?>
            <div class="clearfix"></div>
            
        </div>
    </div>
			<div class="row">
			<?php if(count($albums) > 0): ?>
				<?php $i = 1; ?>
				<?php $__currentLoopData = $albums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $album): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<div class="col-md-4">
						<div class="panel panel-default">
							<div class="panel-body nopadding">
								<div class="widget-card">
									<div class="widget-card-bg">	
										<a href="<?php echo e(url($timeline->username.'/album/show/'.$album->id)); ?>">
											<?php if($album->previewImage()->first() != null): ?> 
												<img src="<?php echo url('/album/'.$album->previewImage()->first()['source']); ?>" alt="<?php echo e($album->name); ?>" title="<?php echo e($album->name); ?>">
											<?php else: ?>
												<img src="<?php echo e(url('/album/'.$album->photos()->first()['source'])); ?>" alt="<?php echo e($album->name); ?>" title="<?php echo e($album->name); ?>">
											<?php endif; ?>
										</a>
									</div>
									<div class="widget-card-project">
										<div class="bridge-text">
											<div class="pull-right">
											<span class="label label-info"><?php echo e($album->privacy); ?></span>
										</div>
											<a href="<?php echo e(url($timeline->username.'/album/show/'.$album->id)); ?>"><?php echo e($album->name); ?> </a>
										</div>
										<div class="upadate-project description">
											<?php echo e(str_limit($album->about, $limit = 39, $end = '...')); ?>

										</div>
										<div class="upadate-project">
											<?php echo e(trans('common.last_updated')); ?> :
											<span> <?php echo e(date('d M y', strtotime($album->updated_at))); ?>

											</span>
										</div>
										
									</div><!-- /widget-card -->
								</div>
							</div>
						</div><!-- /panel -->
					</div>
					<?php if($i % 3 == 0): ?>
						</div><div class="row album-row lightgallery">
					<?php endif; ?>	
					<?php $i++; ?>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			<?php else: ?>
				<div class="col-md-12">
					<div class="alert alert-warning">
						<?php echo e(trans('messages.no_albums')); ?>

					</div>
				</div>
			<?php endif; ?>
			</div>
		</div><!-- /col-md-10 -->
		
	</div>
</div><!-- /container -->

<!-- /main-section -->