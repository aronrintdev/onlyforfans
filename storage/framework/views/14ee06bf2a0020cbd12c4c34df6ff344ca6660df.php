<div class="panel panel-default">
<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<div class="panel-heading no-bg panel-settings">
	<h3 class="panel-title">
		<?php echo e(trans('admin.custom_pages')); ?>	
		<div class="btn-custom btn-rtl">
			<a class="btn btn-success" href="<?php echo e(url('admin/custom-pages/create')); ?>">Create</a>
		</div>
	</h3>

</div>
<div class="panel-body">	
	<div class="announcement-container">	
		<table class="table table-responsive" id="timelines-table">
		    <thead>
		    	<th><?php echo e(trans('admin.title')); ?></th>
		        <th><?php echo e(trans('common.description')); ?></th>
		        <th><?php echo e(trans('common.status')); ?></th>
		        <th colspan="3"><?php echo e(trans('admin.action')); ?></th>
		    </thead>
		    <tbody>
		    <?php $__currentLoopData = $staticpages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staticpage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		        <tr>	        	
		        	<td><?php echo e($staticpage->title); ?></td>
		            <td><?php echo e(str_limit($staticpage->description,50)); ?></td>
		             <?php $status = $staticpage->active == 1 ? trans('admin.active') : trans('admin.inactive'); ?>
		            <td><?php echo e($status); ?></td>
					<td><a href="<?php echo e(url('admin/custom-pages/'.$staticpage->id.'/edit')); ?>"><?php echo e(trans('common.edit')); ?></a></td>              		            
		        </tr>
		    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>			    
		    </tbody>
		</table>			
	</div>
</div>
</div>