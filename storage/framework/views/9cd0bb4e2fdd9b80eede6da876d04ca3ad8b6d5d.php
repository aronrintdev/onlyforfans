<!-- <div class="main-content"> -->
	<div class="container">
		<div class="row">

			<!-- List of user pages-->

			<div class="col-md-6">
				<div class="post-filters pages-groups">
					
					<div class="panel panel-default">
						<div class="panel-heading no-bg panel-settings">
							<div class="side-right create-btn">
								<a href="<?php echo e(url(Auth::user()->username.'/create-page')); ?>" class="btn btn-success"><?php echo e(trans('common.create_page')); ?></a>
							</div>
							<h3 class="panel-title small-heading">
								<?php echo e(trans('messages.pages-manage')); ?>

							</h3>
							
						</div>
						<div class="panel-body">
							<?php if(Auth::user()->own_pages()->count()): ?>
								
							<ul class="list-group page-likes">
								<?php $__currentLoopData = Auth::user()->own_pages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userpage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<li class="list-group-item deletepage">
									<div class="connect-list">
										<div class="connect-link side-left">
											<a href="<?php echo e(url($userpage->username)); ?>">
												<img src=" <?php if($userpage->timeline_id && $userpage->avatar): ?> <?php echo e(url('page/avatar/'.$userpage->avatar)); ?> <?php else: ?> <?php echo e(url('page/avatar/default-page-avatar.png')); ?> <?php endif; ?>" alt="<?php echo e($userpage->name); ?>" title="<?php echo e($userpage->name); ?>"><?php echo e($userpage->name); ?>

											</a>
										</div>
										<div class="side-right">
											<a href="" class="side-right delete-page delete_page" data-pagedelete-id="<?php echo e($userpage->id); ?>"><span class="trash-icon bg-danger"><i class="fa fa-trash text-danger"></i></span></a>
										</div>
										<div class="clearfix"></div>
									</div>
								</li>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</ul>
							<?php else: ?>
								<div class="alert alert-warning">
									<?php echo e(trans('messages.no_pages')); ?>

								</div>
							<?php endif; ?>
						</div>
					</div><!-- /panel -->
				</div>
			</div><!-- /col-md-6 -->
			
			<!-- List of user groups-->
			
			<div class="col-md-6">
				<div class="post-filters pages-groups">
					
					<div class="panel panel-default">
						<div class="panel-heading no-bg panel-settings">
							<h3 class="panel-title small-heading">
								<?php echo e(trans('common.joined_pages')); ?>

							</h3>
						</div>
						<div class="panel-body">
							<?php if(Auth::user()->joinedPages()->count()): ?>
								
							<ul class="list-group page-likes">
								<?php $__currentLoopData = Auth::user()->joinedPages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $joinpage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<li class="list-group-item holder">
									<div class="connect-list">
										<div class="connect-link side-left">
											<a href="<?php echo e(url($joinpage->username)); ?>">
												<img src=" <?php if($joinpage->timeline_id && $joinpage->avatar): ?> <?php echo e(url('page/avatar/'.$joinpage->avatar)); ?> <?php else: ?> <?php echo e(url('page/avatar/default-page-avatar.png')); ?> <?php endif; ?>" alt="<?php echo e($joinpage->name); ?>" title="<?php echo e($joinpage->name); ?>"><?php echo e($joinpage->name); ?>

											</a>
										</div>
										<div class="side-right page-unjoin">
											<div class="left-col">
												<a href="#" class="btn btn-success unjoin-page joined" data-timeline-id="<?php echo e($joinpage->timeline_id); ?>">
													<i class="fa fa-check"></i> <?php echo e(trans('common.joined')); ?>

												</a>
											</div>
										</div>
										<div class="clearfix"></div>
									</div>
								</li>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</ul>
							<?php else: ?>
								<div class="alert alert-warning">
									<?php echo e(trans('messages.no_pages')); ?>

								</div>
							<?php endif; ?>
						</div>
					</div>

				</div><!-- /panel -->
			</div>
		</div><!-- /row -->
	</div>
<!-- </div> --><!-- /main-content -->