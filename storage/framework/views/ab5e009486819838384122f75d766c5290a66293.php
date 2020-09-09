<div class="list-group list-group-navigation fans-group">
    <a href="<?php echo e(url('/admin/general-settings')); ?>" class="list-group-item">

        <div class="list-icon fans-icon <?php echo e(Request::segment(2) == 'general-settings' ? 'active' : ''); ?>">
            <i class="fa fa-shield"></i>
        </div>
        <div class="list-text">
            <span class="badge pull-right"></span>
            <?php echo e(trans('common.website_settings')); ?>

            <div class="text-muted">
                <?php echo e(trans('common.general_website_settings')); ?>

            </div>
        </div>
        <span class="clearfix"></span>
    </a>
    <a href="<?php echo e(url('/admin/user-settings')); ?>" class="list-group-item">

        <div class="list-icon fans-icon <?php echo e(Request::segment(2) == 'user-settings' ? 'active' : ''); ?>">
            <i class="fa fa-user-secret"></i>
        </div>
        <div class="list-text">
            <span class="badge pull-right"></span>
            <?php echo e(trans('common.user_settings')); ?>

            <div class="text-muted">
                <?php echo e(trans('common.user_settings_text')); ?>

            </div>
        </div>
        <span class="clearfix"></span>
    </a>
    <a href="<?php echo e(url('/admin')); ?>" class="list-group-item">

        <div class="list-icon fans-icon <?php echo e((Request::segment(1) == 'admin' && Request::segment(2)==null) ? 'active' : ''); ?>">
            <i class="fa fa-dashboard"></i>
        </div>
        <div class="list-text">
            <span class="badge pull-right"></span>
            <?php echo e(trans('common.dashboard')); ?>

            <div class="text-muted">
                <?php echo e(trans('common.application_statistics')); ?>

            </div>
        </div>
        <span class="clearfix"></span>
    </a>
    <a href="<?php echo e(url('/admin/wallpapers')); ?>" class="list-group-item">
  
        <div class="list-icon fans-icon <?php echo e(Request::segment(2) == 'wallpapers' ? 'active' : ''); ?>">
            <i class="fa fa-picture-o"></i>
        </div>
        <div class="list-text">
            <span class="badge pull-right"></span>
            <?php echo e(trans('common.wallpapers')); ?>

            <div class="text-muted">
                <?php echo e(trans('common.wallpapers_text')); ?>

            </div>
        </div>
        <span class="clearfix"></span>
    </a>

    <a href="<?php echo e(url('/admin/users')); ?>" class="list-group-item">

        <div class="list-icon fans-icon <?php echo e(Request::segment(2) == 'users' ? 'active' : ''); ?>">
            <i class="fa fa-user-plus"></i>
        </div>
        <div class="list-text">
            <span class="badge pull-right"></span>
            <?php echo e(trans('common.manage_users')); ?>

            <div class="text-muted">
                <?php echo e(trans('common.manage_users_text')); ?>

            </div>
        </div>
        <span class="clearfix"></span>
    </a>

    <a href="<?php echo e(url('/admin/manage-reports')); ?>" class="list-group-item">

        <div class="list-icon fans-icon <?php echo e(Request::segment(2) == 'manage-reports' ? 'active' : ''); ?>">
            <i class="fa fa-bug"></i>
        </div>
        
        <div class="list-text">
            <?php if(Auth::user()->getReportsCount() > 0): ?>
            <span class="badge pull-right"><?php echo e(Auth::user()->getReportsCount()); ?></span>
            <?php endif; ?>            
            <?php echo e(trans('common.manage_reports')); ?>

            <div class="text-muted">
                <?php echo e(trans('common.manage_reports_text')); ?>

            </div>             
        </div>
        <span class="clearfix"></span>
    </a>
</div>



