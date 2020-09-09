<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			<?php echo e(trans('admin.active_announcement')); ?>

		</h3>
	</div>
	<div class="panel-body">
	<?php if(Setting::get('announcement') != NULL && $current_anouncement != NULL): ?>
		<div class="announcement-container">	
			<span class="announcement-title">
				<?php echo e($current_anouncement->title); ?>

				<span class="pull-right label label-default expiry-date">
					<?php if($total_days != 0): ?>
						<?php echo e($total_days); ?> <?php echo e(trans('admin.days_to_expire')); ?>

					<?php else: ?>
						<?php echo e(trans('admin.expired')); ?>

					<?php endif; ?>					
				</span>
			</span>
			<div class="clearfix"></div>
			<div class="announcement-description pull-left">
				<?php echo e($current_anouncement->description); ?>

				<div class="time-created">			
					<?php $announces_date = date("F d Y, G:i A", strtotime($current_anouncement->created_at));?>
					<?php echo '<br> Created on '.$announces_date; ?>

				</div>
			</div>
			<span class="pull-right announcement-actions">
				<a href="#" class="view-by"><i class="fa fa-eye"></i> Views : <?php echo e(count($current_anouncement->users)); ?></a>
				<a href="<?php echo e(url('admin/announcements/'.$current_anouncement->id.'/edit')); ?>"><?php echo e(trans('common.edit')); ?></a>
			</span>
		</div>
		<?php else: ?>
			<div class="alert alert-warning "><?php echo e(trans('messages.no_announcements')); ?></div>
		<?php endif; ?>
	</div>
</div>

<div class="panel panel-default">
<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
	<div class="panel-heading no-bg panel-settings">
		<h3 class="panel-title">
			<?php echo e(trans('admin.announcements')); ?>

			<span class="side-right">
				<a href="<?php echo e(url('admin/announcements/create')); ?>" class="btn btn-success"><?php echo e(trans('common.create')); ?></a>
			</span>
		</h3>
	</div>
	<div class="panel-body">
		<div class="announcement-container table-responsive">
			<?php if(count($announcements) > 0): ?>	
				<table class="table announcements-table">
					<thead>
				    	<th><?php echo e(trans('admin.title')); ?></th>
				        <th><?php echo e(trans('common.description')); ?></th>	 
				        <th><?php echo e(trans('admin.start_date')); ?></th>
				        <th><?php echo e(trans('admin.end_date')); ?></th>
				        <th><?php echo e(trans('common.status')); ?></th>
				        <th><?php echo e(trans('admin.action')); ?></th>
			    	</thead>
				    <tbody>
				     <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				    	<tr>
				        	<td><?php echo e($announcement->title); ?></td>
				            <td> 
				            	<span class="description">
				            		<?php echo e($announcement->description); ?> 
				            		
				            			<?php  $announce_date = date("F d Y, G:i A", strtotime($announcement->created_at))  ?>
				            			
				            		<div class="time">
				            			<span class="help-text"><i class="fa fa-eye"></i> <?php echo e(count($announcement->users)); ?>

				            			</span>
				            		</div> 
				            	</span>
							</td> 
							<td>
								<?php echo e($announcement->start_date); ?>

							</td>
							<td>
								<?php echo e($announcement->end_date); ?>

							</td>
							<?php  $status = $announcement->id == Setting::get('announcement') ? trans('admin.active') : trans('admin.inactive')  ?>
							<td>
								<?php if($status == "Active"): ?>
								<a href="<?php echo e(url('admin/activate/'.$announcement->id)); ?>" class="btn btn-success announcement-status" ><?php echo e($status); ?></a>
								<?php else: ?>
								<a href="<?php echo e(url('admin/activate/'.$announcement->id)); ?>" class="btn btn-default announcement-status" ><?php echo e($status); ?></a>
								<?php endif; ?>
							</td>
							<td>
								<ul class="list-inline">	
									<li><a href="<?php echo e(url('admin/announcements/'.$announcement->id.'/edit')); ?>"><span class="pencil-icon bg-success"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span></a></li>
									<li><a href="#" data-announcement-id="<?php echo e($announcement->id); ?>" class="announce-delete"><span class="trash-icon bg-danger"><i class="fa fa-trash" aria-hidden="true"></i></span></a></li>
								</ul>
							</td>
				        </tr>			        
				        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				    </tbody>
				</table>
				<div class="pagination-holder">
					<?php echo e($announcements->render()); ?>

				</div>
			<?php else: ?>
				<div class="alert alert-warning"><?php echo e(trans('messages.no_announcements')); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php echo Theme::asset()->container('footer')->usePath()->add('admin', 'js/admin.js'); ?>

