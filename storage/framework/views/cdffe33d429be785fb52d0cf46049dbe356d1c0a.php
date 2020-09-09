<div class="user-profile-buttons">
	<div class="row follow-links pagelike-links">
		<!-- This [if-1] is for checking current user timeline or diff user timeline -->	
		<?php if(Auth::user()->username != $timeline->username): ?>
		<?php 
					//php code is for checking user's follow_privacy settings
		$user_follow ="";
		$confirm_follow ="";
		$message_privacy ="";						
		$othersSettings = $user->getOthersSettings($timeline->username);
		if($othersSettings)
		{
						//follow_privacy checking
			if ($othersSettings->follow_privacy == "only_follow") {
				$user_follow = "only_follow";
			}elseif ($othersSettings->follow_privacy == "everyone") {
				$user_follow = "everyone";
			}

						//confirm_follow checking
			if ($othersSettings->confirm_follow == "yes") {
				$confirm_follow = "yes";
			}elseif ($othersSettings->confirm_follow == "no") {
				$confirm_follow = "no";
			}

			//message_privacy checking
			if ($othersSettings->message_privacy == "only_follow") {
				$message_privacy = "only_follow";
			}elseif ($othersSettings->message_privacy == "everyone") {
				$message_privacy = "everyone";
			}
		}

		?>
		<!-- This [if-2] is for checking usersettings follow_privacy showing follow/following || message button -->
		<?php if($confirm_follow == "no"): ?>

			<!-- This [if-3] is for checking usersettings follow_privacy showing follow/following || message button -->
			<?php if(($user->followers->contains(Auth::user()->id) && $user_follow == "only_follow") || ($user_follow == "everyone")): ?>

				<?php if(!$user->followers->contains(Auth::user()->id)): ?>
					
					<?php if($message_privacy == "everyone"): ?>
						<div class="col-md-6 col-sm-6 col-xs-6 left-col">
							<a href="#" class="btn btn-options btn-block follow-user btn-default follow" data-timeline-id="<?php echo e($timeline->id); ?>">
								<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

							</a>
						</div>
					<?php else: ?>
						<div class="col-md-12 col-sm-6 col-xs-6">
							<a href="#" class="btn btn-options btn-block follow-user btn-default follow" data-timeline-id="<?php echo e($timeline->id); ?>">
								<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

							</a>
						</div>
					<?php endif; ?>	
					
					<?php if($message_privacy == "everyone"): ?>
						<div class="col-md-6 col-sm-6 col-xs-6 hidden">
							<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="<?php echo e($timeline->id); ?>">
								<i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?>

							</a>
						</div>
					<?php else: ?>
						<div class="col-md-12 col-sm-6 col-xs-6 hidden">
							<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="<?php echo e($timeline->id); ?>">
								<i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?>

							</a>
						</div>
					<?php endif; ?>	
				<?php else: ?>

					<div class="col-md-6 col-sm-6 col-xs-6 hidden">
						<a href="#" class="btn btn-options btn-block follow-user btn-default follow " data-timeline-id="<?php echo e($timeline->id); ?>">
							<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

						</a>
					</div>

					<div class="col-md-6 col-sm-6 col-xs-6 left-col">
						<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="<?php echo e($timeline->id); ?>">	<i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?>

						</a>
					</div>
				<?php endif; ?>
			<?php elseif(($user->following->contains(Auth::user()->id) && $user_follow == "only_follow") || ($user_follow == "everyone")): ?>

				<?php if(!$user->followers->contains(Auth::user()->id)): ?>
					<?php if($message_privacy == "everyone"): ?>	
						<div class="col-md-6 col-sm-6 col-xs-6 left-col">
							<a href="#" class="btn btn-options btn-block follow-user btn-default follow" data-timeline-id="<?php echo e($timeline->id); ?>">
								<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

							</a>
						</div>
					<?php else: ?>
						<div class="col-md-12 col-sm-6 col-xs-6 left-col">
						<a href="#" class="btn btn-options btn-block follow-user btn-default follow" data-timeline-id="<?php echo e($timeline->id); ?>">
							<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

						</a>
					</div>
					<?php endif; ?>
					
					<?php if($message_privacy == "everyone"): ?>
						<div class="col-md-6 col-sm-6 col-xs-6 hidden">
							<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="<?php echo e($timeline->id); ?>">
								<i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?>

							</a>
						</div>
					<?php else: ?>
						<div class="col-md-12 col-sm-6 col-xs-6 hidden">
							<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="<?php echo e($timeline->id); ?>">
								<i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?>

							</a>
						</div>
					<?php endif; ?>

				<?php else: ?>							
					<div class="col-md-6 col-sm-6 col-xs-6 hidden">
						<a href="#" class="btn btn-options btn-block follow-user btn-default follow " data-timeline-id="<?php echo e($timeline->id); ?>">
							<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

						</a>
					</div>

					<div class="col-md-6 col-sm-6 col-xs-6 left-col">
						<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="<?php echo e($timeline->id); ?>">	<i class="fa fa-heart"></i> <?php echo e(trans('common.following')); ?>

						</a>
					</div>
				<?php endif; ?>
			<?php endif; ?>	<!-- End of [if-3]-->

			<?php elseif($confirm_follow == "yes"): ?>
			<!-- This [if-4] is for checking usersettings follow_privacy showing follow/following || message button -->
				<?php if(($user->followers->contains(Auth::user()->id) && $user_follow == "only_follow") || ($user_follow == "everyone")): ?>
					<?php if(!$user->followers->contains(Auth::user()->id)): ?>
						<?php if($message_privacy == "everyone"): ?>
							<div class="col-md-6 col-sm-6 col-xs-6 left-col">
								<a href="#" class="btn btn-options btn-block btn-default follow-user-confirm follow" data-timeline-id="<?php echo e($timeline->id); ?>-<?php echo e($follow_user_status); ?>">
									<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

								</a>
							</div>
						<?php else: ?>
							<div class="col-md-12 col-sm-6 col-xs-6">
								<a href="#" class="btn btn-options btn-block btn-default follow-user-confirm follow" data-timeline-id="<?php echo e($timeline->id); ?>-<?php echo e($follow_user_status); ?>">
									<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

								</a>
							</div>
						<?php endif; ?>
						
						<?php if($message_privacy == "everyone"): ?>
							<div class="col-md-6 col-sm-6 col-xs-6 hidden">
								<a href="#" class="btn btn-options btn-block follow-user-confirm btn-warning followrequest" data-timeline-id="<?php echo e($timeline->id); ?>-<?php echo e($follow_user_status); ?>">
									<i class="fa fa-check"></i> <?php echo e(trans('common.requested')); ?>

								</a>
							</div>
						<?php else: ?>
							<div class="col-md-12 col-sm-6 col-xs-6 hidden">
								<a href="#" class="btn btn-options btn-block follow-user-confirm btn-warning followrequest" data-timeline-id="<?php echo e($timeline->id); ?>-<?php echo e($follow_user_status); ?>">
									<i class="fa fa-check"></i> <?php echo e(trans('common.requested')); ?>

								</a>
							</div>
						<?php endif; ?>	
					<?php else: ?>
						<div class="col-md-6 col-sm-6 col-xs-6 hidden">
							<a href="#" class="btn btn-options btn-block btn-default follow-user-confirm  follow " data-timeline-id="<?php echo e($timeline->id); ?>-<?php echo e($follow_user_status); ?>">
								<i class="fa fa-heart"></i> <?php echo e(trans('common.follow')); ?>

							</a>
						</div>

					<?php if($follow_user_status == "pending"): ?>
					<div class="col-md-6 col-sm-6 col-xs-6 left-col">
						<a href="#" class="btn btn-options btn-block follow-user-confirm btn-warning followrequest" data-timeline-id="<?php echo e($timeline->id); ?>-<?php echo e($follow_user_status); ?>">
							<i class="fa fa-check"></i> <?php echo e(trans('common.requested')); ?>

						</a>
					</div>
					<?php endif; ?>
					<?php if($follow_user_status == "approved"): ?>
					<div class="col-md-6 col-sm-6 col-xs-6 left-col">
						<a href="#" class="btn btn-options btn-block follow-user-confirm btn-primary unfollow" data-timeline-id="<?php echo e($timeline->id); ?>-<?php echo e($follow_user_status); ?>">
							<i class="fa fa-check"></i> <?php echo e(trans('common.following')); ?>

						</a>
					</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>	<!-- End of [if-4]-->
		<?php endif; ?>	<!-- End of [if-2]-->
			<?php if(($user->followers->contains(Auth::user()->id) && $message_privacy == "only_follow") || ($message_privacy == "everyone")): ?>	
				<div class="col-md-6 col-sm-6 col-xs-6 right-col">
					<a href="#" class="btn btn-options btn-block btn-default" onClick="chatBoxes.sendMessage(<?php echo e($timeline->user->id); ?>)">
						<i class="fa fa-inbox"></i> <?php echo e(trans('common.message')); ?>

					</a>
				</div>
			<?php endif; ?>
		<?php else: ?>
		<div class="col-md-12"><a href="<?php echo e(url('/'.Auth::user()->username.'/settings/general')); ?>" class="btn btn-profile"><i class="fa fa-pencil-square-o"></i><?php echo e(trans('common.edit_profile')); ?></a></div>
		<?php endif; ?> <!-- End of [if-1]-->

	</div>
</div>

<?php if(        
        ($timeline->type == 'user' && $timeline->id == Auth::user()->timeline_id) ||
        ($timeline->type == 'page' && $timeline->page->is_admin(Auth::user()->id) == true) ||
        ($timeline->type == 'group' && $timeline->groups->is_admin(Auth::user()->id) == true)
        ): ?>
<br>
<div class="user-profile-buttons">
	<div class="row">
		<div class="col-md-12"><a href="<?php echo e(url('/'.Auth::user()->username.'/settings/wallpaper')); ?>" class="btn btn-profile"><i class="fa fa-image"></i><?php echo e(trans('common.set_wallpaper')); ?></a></div>
	</div>
</div>
<?php endif; ?>

<div class="user-bio-block">
	<div class="bio-header"><?php echo e(trans('common.bio')); ?></div>
	<div class="bio-description">
		<?php echo e(($user->about != NULL) ? $user->about : trans('messages.no_description')); ?>

		<ul class="list-unstyled list-details">
			<?php if($user->hobbies != NULL): ?>
				<li><?php echo '<b>'.trans('common.hobbies').': </b>'; ?> <?php echo e($user->hobbies); ?></li>
			<?php endif; ?>
			<?php if($user->interests != NULL): ?>
				<li><?php echo '<b>'.trans('common.interests').': </b>'; ?> <?php echo e($user->interests); ?></li>
			<?php endif; ?>
			<?php if($user->custom_option1 != NULL && Setting::get('custom_option1') != NULL): ?>
				<li><?php echo '<b>'.Setting::get('custom_option1').': </b>'; ?> <?php echo e($user->custom_option1); ?></li>
			<?php endif; ?>
			<?php if($user->custom_option2 != NULL && Setting::get('custom_option2') != NULL): ?>
				<li><?php echo '<b>'.Setting::get('custom_option2').': </b>'; ?> <?php echo e($user->custom_option2); ?></li>
			<?php endif; ?>
			<?php if($user->custom_option3 != NULL && Setting::get('custom_option3') != NULL): ?>
				<li><?php echo '<b>'.Setting::get('custom_option3').': </b>'; ?> <?php echo e($user->custom_option3); ?></li>
			<?php endif; ?>
			<?php if($user->custom_option4 != NULL && Setting::get('custom_option4') != NULL): ?>
				<li><?php echo '<b>'.Setting::get('custom_option4').': </b>'; ?> <?php echo e($user->custom_option4); ?></li>
			<?php endif; ?>
		</ul>
	</div>
	
	<ul class="bio-list list-unstyled">
		<?php if($user->designation != NULL): ?>
			<li><i class="fa fa-thumb-tack"></i> <span><?php echo e($user->designation); ?></span></li>
		<?php endif; ?>
		<?php if($user->country != NULL): ?>
		<li>
			<i class="fa fa-map-marker" aria-hidden="true"></i><span><?php echo e(trans('common.lives_in').' '.$user->country); ?></span>
		</li>
		<?php endif; ?>

		<?php if($user->city != NULL): ?>
		<li><i class="fa fa-building-o"></i><span><?php echo e(trans('common.from').' '.$user->city); ?></span></li>
		<?php endif; ?>

		<?php if($user->birthday != '1970-01-01'): ?>
		<li><i class="fa fa-calendar"></i><span>

			<?php echo e(trans('common.born_on').' '.date('F d', strtotime($user->birthday))); ?>


		</span></li>
		<?php endif; ?>
	</ul>
	<ul class="list-inline list-unstyled social-links-list">
		<?php if($user->facebook_link != NULL): ?>
			<li>
				<a target="_blank" href="<?php echo e($user->facebook_link); ?>" class="btn btn-facebook"><i class="fa fa-facebook"></i></a>
			</li>
		<?php endif; ?>
		<?php if($user->twitter_link != NULL): ?>
			<li>
				<a target="_blank" href="<?php echo e($user->twitter_link); ?>" class="btn btn-twitter"><i class="fa fa-twitter"></i></a>
			</li>
		<?php endif; ?>
		<?php if($user->dribbble_link != NULL): ?>
			<li>
				<a target="_blank" href="<?php echo e($user->dribbble_link); ?>" class="btn btn-dribbble"><i class="fa fa-dribbble"></i></a>
			</li>
		<?php endif; ?>
		<?php if($user->youtube_link != NULL): ?>
			<li>
				<a target="_blank" href="<?php echo e($user->youtube_link); ?>" class="btn btn-youtube"><i class="fa fa-youtube"></i></a>
			</li>
		<?php endif; ?>
		<?php if($user->instagram_link != NULL): ?>
			<li>
				<a target="_blank" href="<?php echo e($user->instagram_link); ?>" class="btn btn-instagram"><i class="fa fa-instagram"></i></a>
			</li>
		<?php endif; ?>
		<?php if($user->linkedin_link != NULL): ?>
			<li>
				<a target="_blank" href="<?php echo e($user->linkedin_link); ?>" class="btn btn-linkedin"><i class="fa fa-linkedin"></i></a>
			</li>
		<?php endif; ?>
	</ul>
</div>

<!-- Albums Widget -->
<?php if((Auth::user()->username == $timeline->username) ||
	(Auth::user()->username != $timeline->username) && count($timeline->albums()->get()) > 0): ?>
		
		<div class="widget-pictures widget-best-pictures all-groups">
			<div class="picture side-left">
				<span class="font-15"><i class="fa fa-film"></i> &nbsp;<?php echo e(' '.trans('common.albums')); ?></span>
			</div>

			<div class="clearfix"></div>

			<div class="best-pictures my-best-pictures">
				<?php if(count($timeline->albums()->get()) != NULL): ?>
					<div class="row">
						<?php $__currentLoopData = $timeline->albums()->take(4)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $album): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<div class="col-md-6 col-sm-6 col-xs-6 best-pics">
								<a href="<?php echo e(url($timeline->username.'/album/show/'.$album->id)); ?>" class="image-hover" title="<?php echo e($album->name); ?>" data-toggle="tooltip" data-placement="top">
									<?php if($album->previewImage()->first() != null): ?> 
									<span class="image-holder">
										<img src="<?php echo url('/album/'.$album->previewImage()->first()['source']); ?>" alt="<?php echo e($album->name); ?>" title="<?php echo e($album->name); ?>">
										<span class="search"></span>
										<i class="fa fa-search-plus"></i>
									</span>
									<?php else: ?>
									<span class="image-holder">
										<img src="<?php echo e(url('/album/'.$album->photos()->first()['source'])); ?>" alt="<?php echo e($album->name); ?>" title="<?php echo e($album->name); ?>">
										<span class="search"></span>
										<i class="fa fa-search-plus"></i>
									</span>
									<?php endif; ?>
								</a>
							</div>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</div>
					<div>
						<div class="pull-right">
							<a href="<?php echo e(url($timeline->username.'/albums')); ?>" class=""><?php echo e(trans('common.show_all')); ?></a>&nbsp;&nbsp;&nbsp;
						</div>
						<div class="clearfix"></div>
					</div>
				<?php else: ?>
					<div class="alert alert-warning"><?php echo e(trans('messages.no_albums')); ?></div>
				<?php endif; ?>
			</div><!-- /best-pictures -->
		</div>
	<?php endif; ?>

		

<!-- /Albums Widget -->

<!-- Change avatar form -->
<form class="change-avatar-form hidden" action="<?php echo e(url('ajax/change-avatar')); ?>" method="post" enctype="multipart/form-data">
	<input name="timeline_id" value="<?php echo e($timeline->id); ?>" type="hidden">
	<input name="timeline_type" value="<?php echo e($timeline->type); ?>" type="hidden">
	<input class="change-avatar-input hidden" accept="image/jpeg,image/png" type="file" name="change_avatar" >
</form>

<!-- Change cover form -->
<form class="change-cover-form hidden" action="<?php echo e(url('ajax/change-cover')); ?>" method="post" enctype="multipart/form-data">
	<input name="timeline_id" value="<?php echo e($timeline->id); ?>" type="hidden">
	<input name="timeline_type" value="<?php echo e($timeline->type); ?>" type="hidden">
	<input class="change-cover-input hidden" accept="image/jpeg,image/png" type="file" name="change_cover" >
</form>

	<!-- my-pages -->
	<div class="widget-pictures widget-best-pictures all-groups">
		<div class="picture side-left">
			<?php echo e(trans('common.pages')); ?>

		</div>
		<div class="clearfix"></div>
		<div class="best-pictures my-best-pictures scrollable">
			<div class="row">
				<?php if(count($own_pages) > 0): ?>
				<?php $__currentLoopData = $own_pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $own_page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<div class="col-md-2 col-sm-2 col-xs-2 best-pics">
					<a href="<?php echo e(url($own_page->username)); ?>" class="image-hover" title="<?php echo e($own_page->name); ?>" data-toggle="tooltip" data-placement="top">
						<img src="<?php if($own_page->avatar != NULL): ?> <?php echo e(url('page/avatar/'.$own_page->avatar)); ?> <?php else: ?> <?php echo e(url('page/avatar/default-page-avatar.png')); ?> <?php endif; ?>" alt="<?php echo e($own_page->name); ?>" title="<?php echo e($own_page->name); ?>">
					</a>
				</div>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<?php else: ?>
				<div class="alert alert-warning"><?php echo e(trans('messages.no_pages')); ?></div>
				<?php endif; ?>
			</div><!-- /row -->
		</div>
		<div class="show-more-options text-center">
			<?php if(count($own_pages) > 12): ?>
				<a href="#" class="show-all-pages"><i class="fa fa-plus-square-o"></i> <?php echo e(trans('common.show_more')); ?></a>
				<a href="#" class="less-all-pages"><i class="fa fa-minus-square-o"></i> <?php echo e(trans('common.show_less')); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<!-- /my pages -->

	<!-- my-groups -->
	<div class="widget-pictures widget-best-pictures all-groups">
		<div class="picture side-left">
			<?php echo e(trans('common.groups')); ?>

		</div>
		<div class="clearfix"></div>
		<div class="best-pictures my-best-pictures scrollable">
			<div class="row">
				<?php if(count($own_groups) > 0): ?>					
					<?php $__currentLoopData = $own_groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $own_group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<div class="col-md-2 col-sm-2 col-xs-2 best-pics">
						<a href="<?php echo e(url($own_group->username)); ?>" class="image-hover" title="<?php echo e($own_group->name); ?>" data-toggle="tooltip" data-placement="top">
							<img src=" <?php if($own_group->avatar != NULL): ?> <?php echo e(url('group/avatar/'.$own_group->avatar)); ?> <?php else: ?> <?php echo e(url('group/avatar/default-group-avatar.png')); ?> <?php endif; ?> " alt="<?php echo e($own_group->name); ?>" title="<?php echo e($own_group->name); ?>" >
						</a>
					</div>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>					
				<?php else: ?>
					<div class="alert alert-warning"><?php echo e(trans('messages.no_groups')); ?></div>
				<?php endif; ?>
			</div><!-- /row -->
		</div>
		<div class="see-more-options text-center">
			<?php if(count($own_groups) > 12): ?>
				<a href="#" class="show-all-groups"><i class="fa fa-plus-square-o"></i> <?php echo e(trans('common.show_more')); ?></a>
				<a href="#" class="less-all-groups"><i class="fa fa-minus-square-o"></i> <?php echo e(trans('common.show_less')); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<!-- /my pages -->

	<!-- my-events -->
	<div class="widget-pictures widget-best-pictures all-groups">

		<div class="picture pull-left">
			<?php echo e(trans('common.events')); ?>

		</div>
		<div class="clearfix"></div>
		<div class="best-pictures my-best-pictures scrollable">
			<div class="row">
				<?php if(count($user_events) > 0): ?>
					<?php $__currentLoopData = $user_events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user_event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<div class="col-md-2 col-sm-2 col-xs-2 best-pics">
						<a href="<?php echo e(url($user_event->timeline->username)); ?>" class="image-hover" title="<?php echo e($user_event->timeline->name); ?>" data-toggle="tooltip" data-placement="top">
							<img src="<?php echo e($user_event->user->picture); ?>" alt="<?php echo e($user_event->timeline->name); ?>" title="<?php echo e($user_event->timeline->name); ?>" >						
						</a>
					</div>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<?php else: ?>
					<div class="alert alert-warning"><?php echo e(trans('messages.no_events')); ?></div>
				<?php endif; ?>
			</div><!-- /row -->
		</div>
		<div class="see-more-options text-center">
			<?php if(count($user_events) > 12): ?>
				<a href="#" class="show-all-events"><i class="fa fa-plus-square-o"></i> <?php echo e(trans('common.show_more')); ?></a>
				<a href="#" class="less-all-events"><i class="fa fa-minus-square-o"></i> <?php echo e(trans('common.show_less')); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<!-- /my events -->

	<?php if(Setting::get('timeline_ad') != NULL): ?>
	<div id="link_other" class="post-filters">
		<?php echo htmlspecialchars_decode(Setting::get('timeline_ad')); ?> 
	</div>	
	<?php endif; ?>
















