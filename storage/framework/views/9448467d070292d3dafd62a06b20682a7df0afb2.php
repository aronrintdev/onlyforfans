<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<div class="panel panel-default">

	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			<?php echo e($mode.' '.trans('admin.custom_page')); ?>

		</h3>
	</div>
	<div class="panel-body">		
		<?php if($mode =="create"): ?>
			<form method="POST" class="socialite-form" action="<?php echo e(url('admin/custom-pages')); ?>  ">
		<?php else: ?>
			<form method="POST" class="socialite-form" action="<?php echo e(url('admin/custom-pages/'.$staticPage->id.'/update')); ?>">
		<?php endif; ?>

			<?php echo e(csrf_field()); ?>

			<div class="privacy-question">
				<fieldset class="form-group required <?php echo e($errors->has('title') ? ' has-error' : ''); ?>">
					<?php echo e(Form::label('title', trans('admin.page_title'))); ?>

					<?php if($mode == "create"): ?>
						<?php echo e(Form::text('title', NULL , array('class' => 'form-control', 'placeholder' => trans('admin.page_title_placeholder') ))); ?>

					<?php else: ?>
						<?php echo e(Form::text('title', $staticPage->title , array('class' => 'form-control', 'placeholder' => trans('admin.page_title_placeholder') ))); ?>

					<?php endif; ?>	
					<?php if($errors->has('title')): ?>
					<span class="help-block">
						<?php echo e($errors->first('title')); ?>

					</span>
					<?php endif; ?>
				</fieldset>

				<fieldset class="form-group required <?php echo e($errors->has('description') ? ' has-error' : ''); ?>">
					<?php echo e(Form::label('description', trans('admin.page_description'))); ?>

					<?php if($mode == "create"): ?>
						<?php echo e(Form::textarea('description', NULL , array('class' => 'form-control mytextarea', 'placeholder' => trans('messages.create_page_placeholder') ))); ?>

					<?php else: ?>
						<?php echo e(Form::textarea('description', $staticPage->description , array('class' => 'form-control mytextarea', 'placeholder' => trans('messages.create_page_placeholder') ))); ?>

					<?php endif; ?>	
					<?php if($errors->has('description')): ?>
					<span class="help-block">
						<?php echo e($errors->first('description')); ?>

					</span>
					<?php endif; ?>
				</fieldset>

				<fieldset class="form-group">
					<?php echo e(Form::label('active', trans('common.status'))); ?>

					<?php if($mode == "create"): ?>
						<?php echo e(Form::select('active', array(1 => trans('admin.active'), 0 => trans('admin.inactive')), NULL, array('class' => 'form-control'))); ?>

					<?php else: ?>
						<?php echo e(Form::select('active', array(1 => trans('admin.active'), 0 => trans('admin.inactive')), $staticPage->active , array('class' => 'form-control'))); ?>				
					<?php endif; ?>
				</fieldset>
					
				<div class="pull-right">
					<?php echo e(Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success'])); ?>

				</div>
			</div>
		</form>
	</div>
</div><!-- /panel -->