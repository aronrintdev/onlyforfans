<div class="user-profile-buttons">
	<div class="row pagelike-links">
		<?php if($page->is_admin(Auth::user()->id)): ?>
			<?php if(!$page->likes->contains(Auth::user()->id)): ?>								
				<div class="col-md-6 col-sm-6 col-xs-6 left-col"><a href="#" class="btn btn-options btn-block btn-default page-like like" data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-thumbs-up"></i> <?php echo e(trans('common.like')); ?></a></div>
				<div class="col-md-6 col-sm-6 col-xs-6 left-col hidden"><a href="#" class="btn btn-options btn-block btn-success page-like liked " data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-check"></i> <?php echo e(trans('common.liked')); ?></a></div>
			<?php else: ?>
				<div class="col-md-6 col-sm-6 col-xs-6 left-col hidden"><a href="#" class="btn btn-options btn-block btn-default page-like like " data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-thumbs-up"></i> <?php echo e(trans('common.like')); ?></a></div>
				<div class="col-md-6 col-sm-6 col-xs-6 left-col"><a href="#" class="btn btn-options btn-block btn-success page-like liked " data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-check"></i> <?php echo e(trans('common.liked')); ?></a></div>
			<?php endif; ?>

			<div class="col-md-6 col-sm-6 col-xs-6 right-col">
				<a href="<?php echo e(url('/'.$timeline->username.'/page-settings/general')); ?>" class="btn btn-options btn-block btn-default"><i class="fa fa-gear"></i> 
					<?php echo e(trans('common.settings')); ?>

				</a>
			</div>

		<?php else: ?>
			<?php if(!$page->likes->contains(Auth::user()->id)): ?>								
				<div class="col-md-12 col-sm-12 col-xs-12  page"><a href="#" class="btn btn-options btn-block btn-default page-like like" data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-thumbs-up"></i> <?php echo e(trans('common.like')); ?></a></div>
				<div class="col-md-12 col-sm-12 col-xs-12  page hidden"><a href="#" class="btn btn-options btn-block btn-success page-like liked " data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-check"></i> <?php echo e(trans('common.liked')); ?></a></div>
			<?php else: ?>
				<div class="col-md-12 col-sm-12 col-xs-12  page hidden"><a href="#" class="btn btn-options btn-block btn-default page-like like " data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-thumbs-up"></i> <?php echo e(trans('common.like')); ?></a></div>
				<div class="col-md-12 col-sm-12 col-xs-12  page"><a href="#" class="btn btn-options btn-block btn-success page-like liked " data-timeline-id="<?php echo e($timeline->id); ?>"><i class="fa fa-check"></i> <?php echo e(trans('common.liked')); ?></a></div>			
			<?php endif; ?>			

			<div class="col-md-6 col-sm-6 col-xs-6 left-col page hidden">
				<a href="<?php echo e(url('/'.Auth::user()->username.'/settings/general')); ?>" class="btn btn-options btn-block btn-default">
					<i class="fa fa-inbox"></i> <?php echo e(trans('common.messages')); ?>

				</a>
			</div>

		<?php endif; ?>
		</div>
	</div>

	<!-- Change avatar form -->
	<form class="change-avatar-form hidden" action="<?php echo e(url('ajax/change-avatar')); ?>" method="post" enctype="multipart/form-data">
		<input name="timeline_id" value="<?php echo e($timeline->id); ?>" type="hidden">
		<input name="timeline_type" value="<?php echo e($timeline->type); ?>" type="hidden">
		<input class="change-avatar-input hidden" accept="image/jpeg,image/png" type="file" name="change_avatar" >
	</form>

	<!-- Change cover form -->
	<form class="change-cover-form hidden" action="<?php echo e(url('ajax/change-cover')); ?>" method="post" enctype="multipart/form-data">
		<input name="timeline_id" value="<?php echo e($timeline->id); ?>" type="hidden">
		<input name="timeline_type" value="<?php echo e($timeline->type); ?>" type="hidden">
		<input class="change-cover-input hidden" accept="image/jpeg,image/png" type="file" name="change_cover" >
	</form>

	<div class="user-bio-block">
		<div class="bio-header"><?php echo e(trans('common.about')); ?></div>
		<div class="bio-description">
			<?php echo e(($timeline['about'] != NULL) ? $timeline['about'] : trans('messages.no_description')); ?>

		</div>
		<ul class="bio-list list-unstyled">
			<li>
				<i class="fa fa-folder-o" aria-hidden="true"></i><span><?php echo e($page->category->name); ?></span>
			</li>

			<?php if($page->address != null): ?>
				<li>
					<i class="fa fa-map-marker" aria-hidden="true"></i><span><?php echo e($page->address); ?></span>
				</li>
			<?php endif; ?>

			<?php if($page->website != null): ?>
				<li>
					<i class="fa fa-globe" aria-hidden="true"></i><span><?php echo e($page->website); ?></span>
				</li>
			<?php endif; ?>

			<?php if($page->phone != null): ?>
				<li>
					<i class="fa fa-phone" aria-hidden="true"></i><span><?php echo e($page->phone); ?></span>
				</li>
			<?php endif; ?>
		</ul>
	</div>

	<div class="widget-pictures widget-best-pictures"><!-- /pages-liked -->
		<div class="picture side-left">
			<?php echo e(trans('common.members')); ?>

		</div>
		<?php if(count($page_members) > 0): ?>
			<div class="side-right show-all">
				<a href="<?php echo e(url($timeline->username.'/pagemembers')); ?>"><?php echo e(trans('common.show_all')); ?></a>
			</div>
		<?php endif; ?>
		<div class="clearfix"></div>
		<div class="best-pictures my-best-pictures">
			<div class="row">
				<?php if(count($page_members) > 0): ?>
					<?php $__currentLoopData = $page_members->take(12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page_member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<div class="col-md-2 col-sm-2 col-xs-2 best-pics">
						<a href="<?php echo e(url($page_member->username)); ?>" class="image-hover" data-toggle="tooltip" data-placement="top" title="<?php echo e($page_member->name); ?>">
							<img src="<?php echo e($page_member->avatar); ?>" alt="<?php echo e($page_member->name); ?>" title="<?php echo e($page_member->name); ?>">
						</a>
					</div>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<?php else: ?>
					<div class="alert alert-warning"><?php echo e(trans('messages.no_members')); ?></div>
				<?php endif; ?>

			</div>
		</div>
	</div> <!-- /pages-liked -->

