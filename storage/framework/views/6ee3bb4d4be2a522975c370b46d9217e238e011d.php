<li class="commented delete_comment_list comment-replied">
 <div class="comments"> <!-- main-comment -->
  <div class="commenter-avatar">
    <a href="#"><img src="<?php echo e($reply->user->avatar); ?>" title="<?php echo e($reply->user->name); ?>" alt="<?php echo e($reply->user->name); ?>"></a>
  </div>
  <div class="comments-list">
    <div class="commenter">
      <?php if($reply->user_id == Auth::user()->id): ?>
      <a href="#" class="delete-comment delete_comment" data-commentdelete-id="<?php echo e($reply->id); ?>"><i class="fa fa-times"></i></a>
      <?php endif; ?>  
      <div class="commenter-name">
        <a href="<?php echo e(url($reply->user->username)); ?>"><?php echo e($reply->user->name); ?></a><span class="comment-description"><?php echo e($reply->description); ?></span>
      </div>
      <ul class="list-inline comment-options">
        <?php if(!$reply->comments_liked->contains(Auth::user()->id)): ?>
        <li><a href="#" class="text-capitalize like-comment like" data-comment-id="<?php echo e($reply->id); ?>"><?php echo e(trans('common.like')); ?></a></li>
        <li class="hidden"><a href="#" class="text-capitalize like-comment unlike" data-comment-id="<?php echo e($reply->id); ?>"><?php echo e(trans('common.unlike')); ?></a></li>
        <?php else: ?>
        <li class="hidden"><a href="#" class="text-capitalize like-comment like" data-comment-id="<?php echo e($reply->id); ?>"><?php echo e(trans('common.like')); ?></a></li>
        <li><a href="#" class="text-capitalize like-comment unlike" data-comment-id="<?php echo e($reply->id); ?>"><?php echo e(trans('common.unlike')); ?></a></li>
        <?php endif; ?>
        <li>.</li>
        <?php if($reply->comments_liked->count() != null): ?>
        <li><a href="#" class="show-likes like3-<?php echo e($reply->id); ?>"><i class="fa fa-thumbs-up"></i><?php echo e($reply->comments_liked->count()); ?></a></li>
        <li class="show-likes like4-<?php echo e($reply->id); ?> hidden"></li>
        <?php else: ?>
        <li><a href="#" class="show-likes like3-<?php echo e($reply->id); ?>"><i class="fa fa-thumbs-up"></i><?php echo e($reply->comments_liked->count()); ?></a></li>
        <li class="show-likes like4-<?php echo e($reply->id); ?> hidden"></li>
        <?php endif; ?>
        <li>.</li>
        <li>
          <time class="post-time timeago" datetime="<?php echo e($reply->created_at); ?>+00:00" title="<?php echo e($reply->created_at); ?>+00:00"><?php echo e($reply->created_at); ?>+00:00</time>
        </li>
      </ul>
    </div>
  </div>
</div><!-- main-comment -->
</li>
