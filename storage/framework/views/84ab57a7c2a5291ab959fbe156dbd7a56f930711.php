<!-- main-section -->

	<div class="container section-container <?php if($timeline->hide_cover): ?> no-cover <?php endif; ?>">
		<div class="row">
			<div class="col-md-12">
				<?php if($timeline->type == "user"): ?>
					<?php echo Theme::partial('user-header',compact('user','timeline','liked_pages','joined_groups','followRequests','following_count','followers_count','follow_confirm','user_post','joined_groups_count','guest_events')); ?>

				<?php elseif($timeline->type == "page"): ?>
					<?php echo Theme::partial('page-header',compact('page','timeline')); ?>

				<?php elseif($timeline->type == "group"): ?>
					<?php echo Theme::partial('group-header',compact('timeline','group')); ?>

				<?php elseif($timeline->type == "event"): ?>
					<?php echo Theme::partial('event-header',compact('event','timeline')); ?>

				<?php endif; ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-10">

				<div class="row">
					<div class="timeline">
						<div class="col-md-4">
							<?php if($timeline->type == "user"): ?>
							<?php echo Theme::partial('user-leftbar',compact('timeline','user','follow_user_status','own_pages','own_groups','user_events')); ?>

							<?php elseif($timeline->type == "page"): ?>
							<?php echo Theme::partial('page-leftbar',compact('timeline','page','page_members')); ?>

							<?php elseif($timeline->type == "group"): ?>
								<?php echo Theme::partial('group-leftbar',compact('timeline','group','group_members','group_events','ongoing_events','upcoming_events')); ?>

							<?php elseif($timeline->type == "event"): ?>
								<?php echo Theme::partial('event-leftbar',compact('event','timeline')); ?>

							<?php endif; ?>
						</div>

						<!-- Post box on timeline,page,group -->


















































					</div>
				</div><!-- /row -->
			</div><!-- /col-md-10 -->

			<div class="col-md-2">
				<?php echo Theme::partial('timeline-rightbar'); ?>

			</div>

		</div><!-- /row -->
	</div>
