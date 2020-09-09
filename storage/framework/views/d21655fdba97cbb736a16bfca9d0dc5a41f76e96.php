

<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php if(!session()->has('flash_notification.message')): ?>
<div class="alert alert-warning">
	<?php echo e(trans('common.edit_on_risk')); ?>

</div>
<?php endif; ?>

<form action="<?php echo e(url('admin/save-env')); ?>" method="post">
	<?php echo e(csrf_field()); ?>

	<textarea class="form-control" name="env"  rows="30"><?php echo e($env); ?></textarea>

	<br>
	<button type="submit" class="btn pull-right btn-danger"> Save .ENV </button>	
</form>
