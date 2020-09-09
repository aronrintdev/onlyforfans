<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			<?php echo e(trans('common.manage_users')); ?>

		</h3>
	</div>
	<div class="panel-body timeline">
	<div class="col-md-offset-9">			
		<?php echo e(Form::label('sort by', 'Sort by:')); ?>

		<?php echo Form::select('manage_users', array('name_asc' => trans('admin.name_asc'), 'name_desc' => trans('admin.name_desc'), 'created_asc' => trans('admin.created_asc'), 'created_desc' => trans('admin.created_desc')), Request::get('sort'), ['class' => 'form-control usersort']); ?>	
	</div>
	<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

		<?php if(count($users) > 0): ?>
			<div class="table-responsive manage-table">
				<table class="table existing-products-table socialite">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th><?php echo e(trans('admin.id')); ?></th>
							<th><?php echo e(trans('auth.name')); ?></th>
							<th><?php echo e(trans('common.email')); ?></th>
							<th><?php echo e(trans('admin.options')); ?></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
					<?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<tr>
							<td>&nbsp;</td>
							<td><?php echo e($user->id); ?></td>
							<td><a href="#"><img src=" <?php if($user->timeline->avatar_id != null): ?> <?php echo e(url('user/avatar/'.$user->timeline->avatar->source)); ?> <?php else: ?> <?php echo e(url('user/avatar/default-'.$user->gender.'-avatar.png')); ?> <?php endif; ?>" alt="images"></a><a href="<?php echo e(url($user->timeline->username)); ?>"> <?php echo e($user->timeline->name); ?></a></td>
							<td><?php echo e($user->email); ?></td>
							<td>
								<ul class="list-inline">
									<li><a href="<?php echo e(url('admin/users/'.$user->timeline->username.'/edit')); ?>"><span class="pencil-icon bg-success"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span></a></li>
									<li><a href="<?php echo e(url('admin/users/'.$user->timeline->username.'/delete')); ?>" onclick="return confirm('<?php echo e(trans("messages.are_you_sure")); ?>')"><span class="trash-icon bg-danger"><i class="fa fa-trash" aria-hidden="true"></i></span></a></li>
								</ul>
							</td>
							<td>&nbsp;</td>
						</tr>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
















						</tbody>
					</table>
				</div>
				<div class="pagination-holder userpage">
					<?php echo e($users->render()); ?>

				</div>
			<?php else: ?>
				<div class="alert alert-warning"><?php echo e(trans('messages.no_users')); ?></div>
			<?php endif; ?>
		</div>
	</div>
