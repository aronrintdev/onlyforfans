<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			<?php echo e(trans('common.manage_groups')); ?>

		</h3>
	</div>
	<div class="panel-body timeline">
		<div class="col-md-offset-9">
			<?php echo e(Form::label('sort by', 'Sort by:')); ?>

			<?php echo Form::select('manage_users', array('name_asc' => trans('admin.name_asc'), 'name_desc' => trans('admin.name_desc'), 'open_group' => trans('common.open_group'), 'closed_group' => trans('common.closed_group'), 'secret_group' => trans('common.secret_group'), 'member_asc' => trans('common.member_asc'), 'member_desc' => trans('common.member_desc')), Request::get('sort'), ['class' => 'form-control groupsort']); ?>

		</div>

		<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
		<?php if(count($timelines) > 0): ?>
			<div class="table-responsive manage-table">
				<table class="table existing-products-table socialite">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th><?php echo e(trans('admin.id')); ?></th> 
							<th><?php echo e(trans('auth.name')); ?></th>
							<th><?php echo e(trans('common.type')); ?></th>
							<th><?php echo e(trans('common.members')); ?></th> 
							<th><?php echo e(trans('admin.options')); ?></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php $__currentLoopData = $timelines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<tr>
							<td>&nbsp;</td>	
							<td><?php echo e($timeline->groups->id); ?></td>
							<td><a href="#"><img src="<?php if($timeline->groups->avatar): ?> <?php echo e(url('group/avatar/'.$timeline->groups->avatar)); ?> <?php else: ?> <?php echo e(url('group/avatar/default-group-avatar.png')); ?> <?php endif; ?>" alt="<?php echo e($timeline->name); ?>" title="<?php echo e($timeline->name); ?>"></a><a href="<?php echo e(url($timeline->username)); ?>"> <?php echo e($timeline->name); ?></a></td> 
							<td><span class="label label-default"><?php echo e($timeline->groups->type); ?></span></td>
							<td><?php echo e($timeline->groups->users->count()); ?></td> 
							<td>
								<ul class="list-inline">
									<li><a href="<?php echo e(url('admin/groups/'.$timeline->username.'/edit')); ?>"><span class="pencil-icon bg-success"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span></a></li>
									<li><a href="<?php echo e(url('admin/groups/'.$timeline->groups->id.'/delete')); ?>" onclick="return confirm('<?php echo e(trans("messages.are_you_sure")); ?>')"><span class="trash-icon bg-danger"><i class="fa fa-trash text-danger" aria-hidden="true"></i></span></a></li>
								</ul>

							</td>
							<td>&nbsp;</td> 
						</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</tbody>
					</table>
				</div>
				<div class="pagination-holder groupnation">
					<?php echo e($timelines->render()); ?>

				</div>	
			<?php else: ?>
				<div class="alert alert-warning"><?php echo e(trans('messages.no_groups')); ?></div>
			<?php endif; ?>
		</div>
	</div>
