<div class="panel panel-default">
  <div class="panel-heading no-bg panel-settings">  
    <h3 class="panel-title">
      <?php echo e(trans('common.allnotifications')); ?> 
      <?php if(count($notifications) > 0): ?>
        <span class="side-right">
          <a href="<?php echo e(url('allnotifications/delete')); ?>" class="btn btn-danger text-white allnotifications-delete"><?php echo e(trans('common.delete_all')); ?></a>
        </span>
      <?php endif; ?>
    </h3>
  </div>
  <div class="panel-body timeline">  
    <div class="table-responsive">  
      <table class="table apps-table socialite">
        <?php if(count($notifications) > 0): ?>
            <thead>               
                <th></th>
                <th><?php echo e(trans('common.notification')); ?></th>           
                <th><?php echo e(trans('admin.action')); ?></th>
            </thead>
            <tbody>
              <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>              
                  <tr>                                     
                    <td><a href="<?php echo e(url('/'.$notification->notified_from->timeline->username)); ?>">
                        <img src="<?php echo e($notification->notified_from->avatar); ?>" alt="<?php echo e($notification->notified_from->username); ?>" title="<?php echo e($notification->notified_from->name); ?>"></a><a href="<?php echo e(url($notification->notified_from->username)); ?>"></a>
                    </td>
                    <td><?php echo e(str_limit($notification->description,50)); ?></td>                
                    <td><a href="#" data-notification-id="<?php echo e($notification->id); ?>" class="notification-delete"><span class="trash-icon bg-danger"><i class="fa fa-trash" aria-hidden="true"></i></span></a></td>
                  </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                       
            </tbody>            
        <?php else: ?>
          <div class="alert alert-warning"><?php echo e(trans('messages.no_notifications')); ?></div>
          <?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
        <?php endif; ?>        
      </table> 
      <div class="pagination-holder">
        <?php echo e($notifications->render()); ?>

      </div>     
    </div>
  </div>
</div>