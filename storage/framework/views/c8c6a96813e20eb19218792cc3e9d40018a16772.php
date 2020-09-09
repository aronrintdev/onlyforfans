<!-- main-section -->
<!-- <div class="main-content">
 -->	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="post-filters">
					<?php echo Theme::partial('usermenu-settings'); ?>

				</div>
			</div>
			<div class="col-md-8">
				<div class="panel panel-default">
					<div class="panel-heading no-bg panel-settings">
						<h3 class="panel-title">
							<?php echo e(trans('common.my_affiliates')); ?>

						</h3>
					</div>
					<div class="panel-body">
						<div class="affiliate-settings">
							<div class="affiliate-link">
								<h4 class="link-heading">
									<?php echo e(trans('common.affiliate_link')); ?>:
								</h4>
								<a href="<?php echo e(url('register?affiliate='.Auth::user()->username)); ?>"><?php echo e(url('register?affiliate='.Auth::user()->username)); ?></a>
								<div class="affiliate-buttons">
									
									<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(url('register?affiliate='.Auth::user()->username))); ?>" class="btn btn-default fb-xfbml-parse-ignore" target="_blank"><i class="fa fa-facebook-square"></i>Facebook <?php echo e(trans('common.share')); ?></a>

									<a href="https://twitter.com/intent/tweet?text=<?php echo e(url('register?affiliate='.Auth::user()->username)); ?>" class="btn btn-default btn-twitter"><i class="fa fa-twitter-square"></i>Twitter <?php echo e(trans('common.share')); ?></a>
								</div>
							</div>
							<ul class="list-group affliate-followers">
								<?php if(count($referrals) > 0): ?>
								<?php $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<li class="list-group-item">
									<div class="connect-link pull-left">
										<a href="<?php echo e(url('/'.$referral->username)); ?>">
											<img src="<?php echo e($referral->avatar); ?>" alt="<?php echo e($referral->name); ?>" title="<?php echo e($referral->name); ?>">
											<span class="name"><?php echo e($referral->name); ?></span>
										</a>
									</div>
									<!--<?php if(!Auth::user()->following->contains($referral->id)): ?>-->
									<!--<div class="pull-right"><a href="#" class="btn btn-to-follow btn-default follow-user follow" data-timeline-id="<?php echo e($referral->timeline_id); ?>"><i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?> </a></div>-->

									<!--<div class="pull-right hidden"><a href="#" class="btn follow-user btn-success unfollow " data-timeline-id="<?php echo e($referral->timeline_id); ?>"><i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?></a></div>-->
									<!--<?php else: ?>-->
									<!--<div class="pull-right hidden"><a href="#" class="btn btn-to-follow follow-user btn-default follow " data-timeline-id="<?php echo e($referral->timeline_id); ?>"><i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?></a></div>-->
									<!--<div class="pull-right"><a href="#" class="btn follow-user btn-success unfollow" data-timeline-id="<?php echo e($referral->timeline_id); ?>"><i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?></a></div>-->
									<!--<?php endif; ?>-->

									<div class="clearfix"></div>
								</li>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								<?php else: ?>
								<div class="alert alert-warning"><?php echo e(trans('messages.no_affliates')); ?></div>
								<?php endif; ?>
								<div class="pagination">
									<?php if($referrals != NULL): ?>
									<?php echo e($referrals->render()); ?>

									<?php endif; ?>	
								</div>
							</ul>
							
						</div>
					</div>
					
					
				</div>
			</div>
		</div>
	</div>
<!-- </div> -->


	 
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.7";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

    
    <script>window.twttr = (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0],
        t = window.twttr || {};
      if (d.getElementById(id)) return t;
      js = d.createElement(s);
      js.id = id;
      js.src = "https://platform.twitter.com/widgets.js";
      fjs.parentNode.insertBefore(js, fjs);
     
      t._e = [];
      t.ready = function(f) {
        t._e.push(f);
      };
     
      return t;
    }(document, "script", "twitter-wjs"));</script>
</script>