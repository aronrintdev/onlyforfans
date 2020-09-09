<div class="panel panel-default panel-wallpaper">
	<div class="panel-heading no-bg">
		<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
		<div class="page-heading header-text">
			<?php echo e(trans('common.wallpapers')); ?>

		</div>
		<div class="pull-right">
			<form action="<?php echo e(url('admin/wallpapers')); ?>" method="post" class="form-inline" files="true" enctype="multipart/form-data">
				<div class="form-group col-md-offset-2 col-md-6">
					<input type="file" multiple="multiple" name="wallpapers[]" class="wallpapers-input" accept="image/jpeg,image/png,image/gif">
				</div>
				<?php echo e(csrf_field()); ?>

				<div class="form-group col-md-4">
					<button type="submit" class="btn btn-primary btn-downloadreport add-wallpapers"> 
						<i class="fa fa-upload" aria-hidden="true"></i>
						<?php echo e(trans('common.upload')); ?>

					</button>
				</div>
			</form>
		</div>
		<?php $wallpapers = App\Wallpaper::all(); ?>
		<div class="clearfix"></div>

		<?php if(count($wallpapers) <= 0): ?>
		<br>
		<div class="alert alert-warning">
			<?php echo e(trans('messages.no_wallpapers')); ?>

		</div>
		<?php endif; ?>
	</div>
</div>


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
							<a data-sub-html="<h4><?php echo e($wallpaper->title); ?></h4>" href="<?php echo url('/wallpaper/'.$wallpaper->media->source); ?>"  class="btn lightgallery-item btn-default btn-single btn-lightbox btn-sm"><i class="fa fa-search"></i></a>

							<a href="<?php echo e(url('/admin/wallpaper/'.$wallpaper->id.'/delete')); ?>" class="btn btn-default btn-single btn-sm"><i class="fa fa-trash" aria-hidden="true"></i></a>

						</div>
					</div>
				</div>
			</div>
		</div><!-- /panel -->

	</li>
	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php endif; ?>


<?php echo Theme::asset()->container('footer')->usePath()->add('lightgallery', 'js/lightgallery-all.min.js'); ?>