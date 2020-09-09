

<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php if(!session()->has('flash_notification.message')): ?>
<div class="alert alert-warning">
	<?php echo e(trans('common.edit_on_risk')); ?>

</div>
<?php endif; ?>
<?php if($count > 0): ?>
	<form action="<?php echo e(url('admin/update-database')); ?>" method="POST" role="form">
		<?php echo e(csrf_field()); ?>

		<button type="submit" class="btn btn-block btn-danger"><?php echo e(trans('common.update_now')); ?></button>
	</form>
<?php else: ?>
	<div class="alert alert-success">
		<strong><?php echo e(trans('messages.no_pending_updates')); ?></strong>
	</div>
<?php endif; ?>
<br>

<pre class="text-center">
<?php echo $output; ?>

</pre>