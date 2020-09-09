<div class="panel panel-default">
	<div class="panel-heading no-bg panel-settings">
   
		<h3 class="panel-title">
			<?php echo e(trans('common.manage_reports')); ?>

		</h3>
	</div>
	<div class="panel-body nopadding">
        <ul class="nav nav-pills heading-list">
            <li class="active"><a href="#posts" data-toggle="pill" class="header-text"><?php echo e(trans('common.posts')); ?><span><?php echo e(count($post_reports)); ?></span></a></li>
            <li class="divider">&nbsp;</li>
            <li class=""><a href="#users" data-toggle="pill" class="header-text"><?php echo e(trans('common.users')); ?><span><?php echo e(count($user_reports)); ?></span></a></li>
            <li class="divider">&nbsp;</li>
        </ul>
    </div>
    <div class="tab-content nopadding">
        <div id="posts" class="tab-pane fade active in">
            <table class="table apps-table">
             <?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            <?php if(count($post_reports) > 0): ?>
                <thead>                
                    <tr>                        
                        <th><?php echo e(trans('admin.reported_by')); ?></th>
                        <th><?php echo e(trans('common.post')); ?></th>
                        <th><?php echo e(trans('common.status')); ?></th>
                        <th colspan="2">&nbsp;</th>                        
                    </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $post_reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post_report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>                       
                        <td>
                            <a href="<?php echo e(url($user->find($post_report->reporter_id)->username)); ?>"><img src="<?php echo e($user->find($post_report->reporter_id)->avatar); ?>" alt="<?php echo e($user->find($post_report->reporter_id)->name); ?>" title="<?php echo e($user->find($post_report->reporter_id)->name); ?>"></a>

                            <div class="app-details">
                                <div class="app-name">
                                    <a href="<?php echo e(url($user->find($post_report->reporter_id)->username)); ?>"><?php echo e($user->find($post_report->reporter_id)->name); ?></a>
                                </div>
                                <div class="text-secondary">
                                    <?php echo e($user->find($post_report->reporter_id)->username); ?>

                                </div>
                            </div>
                        </td>

                        <td>
                            <a href="<?php echo e(url('post/'.$post_report->post_id)); ?>"><?php echo e(trans('admin.show_post')); ?></a>
                            <div class="text-secondary">
                                <?php echo e(trans('admin.id')); ?>: <?php echo e($post_report->post_id); ?>

                            </div>
                        </td>

                        <td>
                            <span class="label label-default"><?php echo e($post_report->status); ?></span>
                          
                        </td>

                        <td>
                            <a href="<?php echo e(url('admin/mark-safe/'.$post_report->id)); ?>"><span class="label label-success"><?php echo e(trans('admin.mark_safe')); ?></span></a>
                        </td>

                        <td>
                            <a href="<?php echo e(url('admin/delete-post/'.$post_report->id.'/'.$post_report->post_id)); ?>" onclick="return confirm('<?php echo e(trans("messages.are_you_sure")); ?>')"><span class="label label-danger"><?php echo e(trans('admin.delete_post')); ?></span></a>
                        </td>                        
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            <?php else: ?>
                <div class="alert alert-warning"><?php echo e(trans('messages.no_reports')); ?></div>
            <?php endif; ?>    
            </table>
        </div>
<!-- End of post tab-->

        <!--Start Users tab-->
        <div id="users" class="tab-pane fade">
            <table class="table apps-table">
                <?php if(count($user_reports) > 0): ?>
                <thead>
                    <tr>                       
                        <th><?php echo e(trans('admin.reported_by')); ?></th>
                        <th><?php echo e(trans('common.user')); ?></th>
                        <th><?php echo e(trans('common.status')); ?></th>                       
                        <th colspan="2">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $user_reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user_report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                           <a href="<?php echo e(url($user->find($user_report->reporter_id)->username)); ?>"><img src="<?php echo e($user->find($user_report->reporter_id)->avatar); ?>" alt="<?php echo e($user->find($user_report->reporter_id)->name); ?>" title="<?php echo e($user->find($user_report->reporter_id)->name); ?>"></a>
                            
                            <div class="app-details">
                                <div class="app-name">
                                    <a href="<?php echo e(url($user->find($user_report->reporter_id)->username)); ?>"><?php echo e($user->find($user_report->reporter_id)->name); ?></a>
                                </div>
                                <div class="text-secondary">
                                    <?php echo e($user->find($user_report->reporter_id)->username); ?>

                                </div>
                            </div>
                        </td>
                        <td>
                           <a href="<?php echo e(url($timeline->find($user_report->timeline_id)->username)); ?>">

                                <img src="<?php echo e($timeline->find($user_report->timeline_id)->user->avatar); ?>" alt="<?php echo e($timeline->find($user_report->timeline_id)->name); ?>" title="<?php echo e($timeline->find($user_report->timeline_id)->name); ?>">
                            </a>
                            
                            <div class="app-details">
                                <div class="app-name">
                                    <a href="<?php echo e(url($timeline->find($user_report->timeline_id)->username)); ?>"><?php echo e($timeline->find($user_report->timeline_id)->name); ?></a>
                                </div>
                                <div class="text-secondary">
                                    <?php echo e($timeline->find($user_report->timeline_id)->username); ?>

                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="label label-default"><?php echo e($user_report->status); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo e(url('admin/markpage-safe/'.$user_report->id)); ?>"><span class="label label-success"><?php echo e(trans('admin.mark_safe')); ?></span></a>
                        </td>
                        <td>
                            <a href="<?php echo e(url('admin/deleteuser/'.$timeline->find($user_report->timeline_id)->username)); ?>" onclick="return confirm('<?php echo e(trans("messages.are_you_sure")); ?>')"><span class="label label-danger"><?php echo e(trans('admin.delete_user')); ?></span></a>
                            
                        </td>
                     </tr>          
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            <?php else: ?>
                <div class="alert alert-warning"><?php echo e(trans('messages.no_reports')); ?></div>
            <?php endif; ?>
            </table>
        </div>
        <!-- End of user tab-->

        <!-- Start Page tab-->
        <div id="pages" class="tab-pane fade">
            <table class="table apps-table">
            <?php if(count($page_reports) > 0): ?>
                <thead>
                    <tr>                       
                        <th><?php echo e(trans('admin.reported_by')); ?></th>
                        <th><?php echo e(trans('admin.page')); ?></th>
                        <th><?php echo e(trans('common.status')); ?></th>                       
                        <th colspan="2">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $page_reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page_report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <tr>
                        <td>
                           <a href="<?php echo e(url($user->find($page_report->reporter_id)->username)); ?>"><img src="<?php echo e($user->find($page_report->reporter_id)->avatar); ?>" alt="<?php echo e($user->find($page_report->reporter_id)->name); ?>" title="<?php echo e($user->find($page_report->reporter_id)->name); ?>"></a>
                            
                            <div class="app-details">
                                <div class="app-name">
                                    <a href="<?php echo e(url($user->find($page_report->reporter_id)->username)); ?>"><?php echo e($user->find($page_report->reporter_id)->name); ?></a>
                                </div>
                                <div class="text-secondary">
                                    <?php echo e($user->find($page_report->reporter_id)->username); ?>

                                </div>
                            </div>
                        </td>
                        <td>
                           <a href="<?php echo e(url($timeline->find($page_report->timeline_id)->username)); ?>">

                                <img src="<?php echo e($timeline->find($page_report->timeline_id)->avatar ? url('page/avatar'.$timeline->find($page_report->timeline_id)->avatar) : url('page/avatar/default-page-avatar.png')); ?>" alt="<?php echo e($timeline->find($page_report->timeline_id)->name); ?>" title="<?php echo e($timeline->find($page_report->timeline_id)->name); ?>">
                            </a>
                            
                            <div class="app-details">
                                <div class="app-name">
                                    <a href="<?php echo e(url($timeline->find($page_report->timeline_id)->username)); ?>"><?php echo e($timeline->find($page_report->timeline_id)->name); ?></a>
                                </div>
                                <div class="text-secondary">
                                    <?php echo e($timeline->find($page_report->timeline_id)->username); ?>

                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="label label-default"><?php echo e($page_report->status); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo e(url('admin/markpage-safe/'.$page_report->id)); ?>"><span class="label label-success"><?php echo e(trans('admin.mark_safe')); ?></span></a>
                        </td>
                        <td>
                            <a href="<?php echo e(url('admin/deletepage/'.$timeline->find($page_report->timeline_id)->page->id.'/true')); ?>" onclick="return confirm('<?php echo e(trans("messages.are_you_sure")); ?>')"><span class="label label-danger"><?php echo e(trans('admin.delete_page')); ?></span></a>
                        </td>
                     </tr>          
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            <?php else: ?>
                <div class="alert alert-warning"><?php echo e(trans('messages.no_reports')); ?></div>
            <?php endif; ?>
            </table>
        </div>
        <!-- End of page tab-->

        <!-- Start Group tab-->
        <div id="groups" class="tab-pane fade">
            <table class="table apps-table">
           <?php if(count($group_reports) > 0): ?>
                <thead>
                    <tr>                       
                        <th><?php echo e(trans('admin.reported_by')); ?></th>
                        <th><?php echo e(trans('common.group')); ?></th>                       
                        <th><?php echo e(trans('common.status')); ?></th>                       
                        <th colspan="2">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $group_reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group_report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                           <a href="<?php echo e(url($user->find($group_report->reporter_id)->username)); ?>"><img src="<?php echo e($user->find($group_report->reporter_id)->avatar); ?>" alt="<?php echo e($user->find($group_report->reporter_id)->name); ?>" title="<?php echo e($user->find($group_report->reporter_id)->name); ?>"></a>
                            
                            <div class="app-details">
                                <div class="app-name">
                                    <a href="<?php echo e(url($user->find($group_report->reporter_id)->username)); ?>"><?php echo e($user->find($group_report->reporter_id)->name); ?></a>
                                </div>
                                <div class="text-secondary">
                                    <?php echo e($user->find($group_report->reporter_id)->username); ?>

                                </div>
                            </div>
                        </td>
                        <td>
                           <a href="<?php echo e(url($timeline->find($group_report->timeline_id)->username)); ?>">

                                <img src="<?php echo e($timeline->find($group_report->timeline_id)->avatar ? url('group/avatar'.$timeline->find($group_report->timeline_id)->avatar) : url('group/avatar/default-group-avatar.png')); ?>" alt="<?php echo e($timeline->find($group_report->timeline_id)->name); ?>" title="<?php echo e($timeline->find($group_report->timeline_id)->name); ?>">
                            </a>
                            
                            <div class="app-details">
                                <div class="app-name">
                                    <a href="<?php echo e(url($timeline->find($group_report->timeline_id)->username)); ?>"><?php echo e($timeline->find($group_report->timeline_id)->name); ?></a>
                                </div>
                                <div class="text-secondary">
                                    <?php echo e($timeline->find($group_report->timeline_id)->username); ?>

                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="label label-default"><?php echo e($group_report->status); ?></span>
                        </td>
                        <td>
                            <a href="<?php echo e(url('admin/markpage-safe/'.$group_report->id)); ?>"><span class="label label-success"><?php echo e(trans('admin.mark_safe')); ?></span></a>
                        </td>
                        <td>
                            <a href="<?php echo e(url('admin/deletegroup/'.$timeline->find($group_report->timeline_id)->groups->id)); ?>" onclick="return confirm('<?php echo e(trans("messages.are_you_sure")); ?>')"><span class="label label-danger"><?php echo e(trans('admin.delete_group')); ?></span></a>
                        </td>
                     </tr>  
                     
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            <?php else: ?>
                <div class="alert alert-warning"><?php echo e(trans('messages.no_reports')); ?></div>
            <?php endif; ?>
            </table>
        </div>
        <!-- End of group tab-->
    </div>
	<div class="panel-body timeline hidden">
	<?php echo $__env->make('flash::message', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
		<?php if(count($post_reports) > 0): ?>
			<div class="table-responsive">
				<table class="table existing-products-table">
					<thead>
						<tr>
							<th><?php echo e(trans('admin.id')); ?></th> 
							<th><?php echo e(trans('admin.reported_by')); ?></th>
							<th><?php echo e(trans('common.post')); ?></th> 
							<th><?php echo e(trans('common.status')); ?></th>
							<th><?php echo e(trans('admin.options')); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $__currentLoopData = $post_reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post_report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<tr>	
							<td><?php echo e($post_report->id); ?></td>
							<td><a href="#"><img src="<?php if($post->getAvatar($post_report->reporter_id)): ?> <?php echo e(url('user/avatar/'.$post->getAvatar($post_report->reporter_id))); ?> <?php else: ?> <?php echo e(url('user/avatar/default-'.$post->getGender($post_report->reporter_id).'-avatar.png')); ?> <?php endif; ?>" alt="images"></a><a href="<?php echo e(url($post->getUserName($post_report->reporter_id))); ?>"> <?php echo e($post->getUserName($post_report->reporter_id)); ?></a></td>

							<td><a href="#"><?php echo e(trans('admin.show_post')); ?></a</td> 
							<td><?php echo e($post_report->status); ?></td>
							<td>
								<a href="<?php echo e(url('admin/mark-safe/'.$post_report->id)); ?>" class="btn btn-success"><i class="fa fa-thumbs-up"></i><?php echo e(trans('admin.mark_safe')); ?></a>
								<a href="<?php echo e(url('admin/delete-post/'.$post_report->id.'/'.$post_report->post_id)); ?>" class="btn btn-danger" onclick="return confirm('<?php echo e(trans("messages.are_you_sure")); ?>')"><i class="fa fa-thumbs-down"></i><?php echo e(trans('admin.delete_post')); ?></a>

							</td> 
						</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</tbody>
					</table>
				</div>
				<?php else: ?>
				<div class="alert alert-warning hidden"><?php echo e(trans('messages.no_reports')); ?></div>
			<?php endif; ?>
		</div>
	</div>
