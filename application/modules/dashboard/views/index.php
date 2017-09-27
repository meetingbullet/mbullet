<?php
$has_new_homework = false;
foreach ($my_todo['homeworks'] as $meeting_key => $homeworks) {
	foreach ($homeworks as $homework) {
		if ($homework->is_read == 0) {
			$has_new_homework = true;
			break;
		}
	}

	if ($has_new_homework) break;
}

$has_new_project = false;
foreach ($my_projects as $project) {
	if ($project->is_read == 0) {
		$has_new_project = true;
		break;
	}
}

$has_new_upcoming_meeting = false;
foreach($my_todo['today_meetings'] as $meeting) {
	if ($meeting->is_read == 0) {
		$has_new_upcoming_meeting = true;
		break;
	}
}

$evaluates_count = 0;
$has_new_rating = false;
foreach ($my_todo['evaluates'] as $evaluate) {
	$evaluates_count += count($evaluate->evaluates);

	foreach ($evaluate->evaluates as $rating) {
		if ( !$rating->is_read) {
			$has_new_rating = true;
			break;
		}
	}
}

?>
<div class="main-container">
	<div class="an-sidebar-nav js-sidebar-toggle-with-click">
		<h3 class="an-sidebar-logo">
			<a class="an-logo-link" href="<?php echo site_url(DEFAULT_LOGIN_LOCATION); ?>">
				<img src="<?php echo base_url('assets/images/logo-black.svg'); ?>" 
						alt="<?php echo $this->settings_lib->item('site.title'); ?> " width="120"></a>
		</h3>

		<ul class="an-main-nav">
			<?php if (has_permission('Project.Create')) : ?>
			<li class="an-nav-item">
				<a href="#" class="mb-open-modal" data-modal-id="create-project-modal" data-url="<?php echo site_url('project/create') ?>">
					<i class="ion-ios-plus-outline"></i> <?php echo lang('db_create_project') ?>
				</a>
			</li>
			<?php endif ?>
			<li class="an-nav-item">
				<a id="my-todo" class="js-show-child-nav nav-open" href="#">
					<i class="ion-ios-copy-outline"></i>
					<span class="nav-title">My To Do
					<?php if ($has_new_homework):?>
					<span class="badge badge-warning badge-bordered badge-todo-new">new</span>
					<?php endif; ?>

					<?php if ($my_todo['homeworks_count'] + $evaluates_count + count($my_todo['today_meetings']) > 0): ?>
					<span class="count"><?php echo $my_todo['homeworks_count'] + $evaluates_count + count($my_todo['today_meetings']) ?></span>
					<?php endif; ?>
					</span>
				</a>

				<ul class="an-child-nav js-open-nav" style="display: block;">
					<li>
						<a href="#" class="js-show-child-nav" 
							data-toggle="popover" 
							data-placement="right" 
							title="<?php echo lang('db_homework') ?>" 
							id="homework">

							<i class="ion-ios-book-outline"></i>
							<?php echo lang('db_homework') ?>
							<?php if ($has_new_homework):?>
							<span class="badge badge-warning badge-bordered badge-homework-new">new</span>
							<?php endif; ?>
							<?php if ($my_todo['homeworks_count'] > 0): ?>
							<span class="badge badge-primary pull-right homework-counter"><?php echo $my_todo['homeworks_count'] ?></span>
							<?php endif; ?>
						</a>
					</li>
					<li>
						<a href="#" class="js-show-child-nav" 
							data-toggle="popover" 
							data-placement="right" 
							title="<?php echo lang('db_rate') ?>" 
							id="open-rate">
							<i class="ion-ios-star-outline"></i>
							<?php echo lang('db_rate') ?>
							<?php if ($has_new_rating):?>
							<span class="badge badge-warning badge-bordered badge-rate-new">new</span>
							<?php endif; ?>
							<?php if ($evaluates_count > 0): ?>
							<span class="badge badge-primary pull-right"><?php echo $evaluates_count ?></span>
							<?php endif; ?>
						</a>
					</li>
					<li>
						<a href="#" class="js-show-child-nav" 
							data-toggle="popover" 
							data-placement="right" 
							title="<?php echo lang('db_today_meetings') ?>" 
							id="today-meetings">
							<i class="ion-chatbox-working"></i>
							<?php echo lang('db_today_meetings') ?>
							<?php if (count($my_todo['today_meetings']) > 0): ?>
							<span class="badge badge-primary pull-right"><?php echo count($my_todo['today_meetings']) ?></span>
							<?php endif; ?>
							<?php if ($has_new_upcoming_meeting):?>
							<span class="badge badge-warning badge-bordered badge-todo-new">new</span>
							<?php endif; ?>
						</a>
					</li>
				</ul>
			</li>

			<!-- My Projects -->
			<li class="an-nav-item">
				<a id="my-project" class="js-show-child-nav" href="#">
					<i class="ion-ios-briefcase-outline"></i>
					<span class="nav-title">My Projects

					<?php if ($has_new_project):?>
					<span class="badge badge-warning badge-bordered badge-new">new</span>
					<?php endif; ?>
					<?php if (count($my_projects) > 0): ?>
					<span class="count"><?php e(count($my_projects)) ?></span>
					<?php endif; ?>
					</span>
				</a>

				<ul class="an-child-nav js-open-nav" style="display: none;">
					<?php foreach ($my_projects AS $project): ?>
					<li class='project' data-project-id="<?php echo $project->project_id ?>">
						<a 	href="<?php echo site_url('project/' . $project->cost_code)?>" 
							class='js-show-child-nav mb-popover-project <?php if ( !$project->is_read) echo 'new' ?>' 
							data-project-id="<?php echo $project->project_id ?>" 
							data-name="<?php echo $project->name ?>" 
							data-owned="<?php echo $project->owned_by_x ?>"
							data-cost-code="<?php echo $project->cost_code ?>" 
							data-team="<?php echo $project->member_number ?>" 

							<?php if ($project->is_unspecified_project) echo 'style="text-transform: uppercase;"' ?>
						>
							<?php if ($project->is_unspecified_project) : ?>
							<i class="ion-help-circled"></i>
							<?php endif ?>
							<?php echo ($project->name . " <b>[{$project->cost_code}]</b>") ?>
							<?php if ( ! $project->is_read): ?>
							<span class="badge badge-warning badge-bordered badge-new">new</span>
							<?php endif; ?>
						</a>

						<ul class="an-child-nav js-open-nav">
							<li class="mb-child-nav" data-order="2">
								<a href='#'>
									<i class="ion-ios-pulse-strong"></i>
									<?php echo lang('db_summary') ?>
								</a>
							</li>
							<li class="mb-child-nav" data-order="3">
								<a href='#'>
									<i class="ion-ios-people"></i>
									<?php echo lang('db_team') ?>
								</a>
							</li>
							<li class="mb-child-nav" data-order="4">
								<a href='#'>
									<i class="ion-ios-pie"></i>
									<?php echo lang('db_statistics') ?>
								</a>
							</li>
						</ul>
					</li>
					<?php endforeach; ?>
				</ul>
			</li> <!-- /My Projects -->

			<!-- Other Projects -->
			<?php if (has_permission('Project.View.All')) :?>
			<li class="an-nav-item">
				<a id="other-project" class="js-show-child-nav" href="#">
					<i class="ion-ios-briefcase-outline"></i>
					<span class="nav-title">Other Projects
					<?php if (count($other_projects) > 0): ?>
					<span class="count"><?php e(count($other_projects)) ?></span>
					<?php endif; ?>
				</a>

				<ul class="an-child-nav js-open-nav" style="display: none;">
					<?php foreach ($other_projects AS $project): ?>
					<li>
						<a 	href="<?php echo site_url('project/' . $project->cost_code)?>" 
							class='mb-popover-project' 
							data-project-id="<?php echo $project->project_id ?>" 
							data-name="<?php echo $project->name ?>" 
							data-owned="<?php echo $project->owned_by_x ?>"
							data-cost-code="<?php echo $project->cost_code ?>" 
							data-team="<?php echo $project->member_number ?>" 
							data-type="other"
							>
							<?php echo ($project->name . " <b>[{$project->cost_code}]</b>") ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</li> <!-- /Other Projects -->
			<?php endif; ?>

		</ul> <!-- end .AN-MAIN-NAV -->

		<?php //if (has_permission('Project.Create')) : ?>
		<!--ul class="an-main-nav bottom">
			<li class="an-nav-item">
				<a href="#" class="mb-open-modal" data-modal-id="create-project-modal" data-url="<?php echo site_url('project/create') ?>">
					<i class="ion-ios-plus-outline"></i> <?php echo lang('db_create_project') ?>
				</a>
			</li>
		</ul> <!-- end .AN-MAIN-NAV.BOTTOM -->
		<?php //endif; ?>
	</div> <!-- /.an-sidebar-nav -->

	<div class="an-page-content">
		<header class="an-header wow fadeInDown">
			<div class="an-topbar-left-part">
			</div> <!-- end .AN-TOPBAR-LEFT-PART -->

			<div class="an-topbar-right-part">

				<?php /*
				<div class="an-notifications">
					<div class="btn-group an-notifications-dropown notifications">
					<button type="button" class="an-btn an-btn-icon dropdown-toggle js-has-new-notification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="ion-ios-bell-outline"></i>
					</button>
					</div>
				</div> <!-- end .AN-NOTIFICATION -->

				<div class="an-messages">
					<div class="btn-group an-notifications-dropown messages">
					<button type="button" class="an-btn an-btn-icon dropdown-toggle js-has-new-messages" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="ion-ios-email-outline"></i>
					</button>
					</div>
				</div> <!-- end .AN-MESSAGE -->
				*/ ?>
				<div class="an-settings">
					<div class="btn-group an-notifications-dropown settings">
						<button type="button" class="an-btn an-btn-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ion-ios-gear-outline"></i>
						</button>
						<div class="dropdown-menu">
							<p class="an-info-count">Settings</p>
							<ul class="an-settings-list">
								<!--
								<li><a href="<?php echo site_url('admin/settings'); ?>"><i class="ion-ios-settings"></i>Preferences</a></li>
								-->
								<li><a href="<?php echo site_url('admin/team'); ?>"><i class="ion-ios-people-outline"></i>Team</a></li>
								<!--
								<li><a href="<?php echo site_url('admin/invites'); ?>"><i class="ion-ios-personadd-outline"></i>Invitations</a></li>
								<li><a href="<?php echo site_url('admin/billing'); ?>"><i class="ion-social-usd-outline"></i>Billing</a></li>
								<li><a href="<?php echo site_url('admin/auth'); ?>"><i class="ion-ios-locked-outline"></i>Authentication</a></li>
								-->
							</ul>
						</div>
					</div>
				</div>

				<div class="an-profile-settings">
					<div class="btn-group an-notifications-dropown profile">
						<button type="button" class="an-btn an-btn-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="an-profile-img" style="background-image: url('<?php
							if (empty($current_user->avatar)) echo gravatar_url($current_user->email, 40);
							elseif (!filter_var($current_user->avatar, FILTER_VALIDATE_URL) === false)	echo $current_user->avatar;
							else echo $current_user->avatar ? img_path() . 'users/' . $current_user->avatar : img_path() . 'default_avatar.png'; 
							?>');"></span>
							<div class="user-info">
								<span class="an-user-name"><?php echo $current_user->first_name . ' ' . $current_user->last_name ?></span>
								<div class="rate">
									<span class="average-star">
										<?php 
											$counting_stars = $user->avarage_rate;
											$all_star = 5;
											while ($all_star --) {

												if ($counting_stars-- > 0) {
													echo '<i class="ion-android-star"></i>';
												} else {
													echo '<i class="ion-android-star-outline"></i>';
												}
											}
										?>
									</span>
								</div>
								<div class="total-points"><?php e(sprintf(lang('db_total_xp'), empty($user->total_xp) ? 0 : $user->total_xp)) ?></div>
							</div>
							<span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
						</button>
						<div class="dropdown-menu">
							<p class="an-info-count">Profile</p>
							<ul class="an-profile-list">
								<li><a href="<?php e(site_url() . 'users/profile')?>"><i class="icon-user"></i>My profile</a></li>
								<li><a href="<?php e(site_url() . 'logout')?>"><i class="icon-download-left"></i><?php e(lang('us_logout'))?></a></li>
							</ul>
						</div>
					</div>
				</div> <!-- end .AN-PROFILE-SETTINGS -->
			</div> <!-- end .AN-TOPBAR-RIGHT-PART -->
		</header> <!-- end .AN-HEADER -->

		<div class="an-content-body">
			<div class="alert-wrapper">
				<?php echo $this->mb_project->meeting_alert(); ?>
				<?php echo $this->mb_project->meeting_invitations(); ?>
			</div>
			<div class="calendar-wrapper">
				<div class="heading-wrapper">
					<h1 class="db-h1">
						<span class="an-settings-button">
							<?php echo lang('st_meetings') ?>
						</span>
					</h1>
				</div>
				<div id="calendar"></div>
			</div><!-- .calendar-wrapper -->
		</div> <!-- end .AN-CONTENT-BODY -->

		<footer class="an-footer">
			<p>COPYRIGHT 2017 © GEAR INC. ALL RIGHTS RESERVED</p>
		</footer> <!-- end an-footer -->
	</div> <!-- .an-page-content -->
</div>

<div id="template">
	<div id="homework-popover" style="display: none">
		<div id="homework-content" class="mb-popover-content">
			<table class="table">
				<thead>
					<tr>
						<th>Project</th>
						<th>ID</th>
						<th>Meeting</th>
						<th class="text-center">Date</th>
						<th class="text-center">Time</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$i = 1; 
					foreach ($my_todo['homeworks'] as $meeting_key => $homework): 
						$scheduled_end_time = date_create_from_format('Y-m-d H:i:s', $homework[0]->scheduled_start_time);
						$scheduled_end_time->modify('+' . $homework[0]->in . ' ' . $homework[0]->in_type);

						$scheduled_start_date = display_time($homework[0]->scheduled_start_time, null, 'l jS F');

						$scheduled_start_time = display_time($homework[0]->scheduled_start_time, null, 'H:ia');
						$scheduled_end_time = display_time($scheduled_end_time->format('Y-m-d H:i:s'), null, 'H:ia');
					?>
					<tr data-homework-id="<?php echo $homework[0]->homework_id ?>" class='parent'>
						<td><?php echo $meeting_key ?></td>
						<td><?php echo $i ?></td>
						<td><?php echo $homework[0]->meeting_name ?></td>
						<td class="text-center"><?php echo $scheduled_start_date ?></td>
						<td class="text-center"><?php echo $scheduled_start_time . ' - ' . $scheduled_end_time ?></td>
						<td><!--<i class="indicator glyphicon glyphicon-chevron-up pull-right"></i>--></td>
					</tr>
					<tr data-homework-id="<?php echo $homework[0]->homework_id ?>" class='child-head'>
						<th></th>
						<th></th>
						<th>To Do</th>
						<th></th>
						<th class="text-center">Attachments</th>
						<th class="text-center">Confirm</th>
					</tr>
					<?php $j = 1; foreach ($homework as $hw):?>
					<tr data-homework-id="<?php echo $hw->homework_id ?>" class='child<?php echo iif ( ! $hw->is_read, ' new') ?>'>
						<td>
							<?php if ( ! $hw->is_read): ?>
							<span class="badge badge-warning badge-bordered badge-homework-new">new</span>
							<?php endif; ?>
						</td>
						<td><?php echo $i .'.'. $j++ ?></td>
						<td colspan="2"><?php echo $hw->name ?></td>
						<td class="text-center">
							<?php if ($hw->attachments): ?>
							<div class="attachment">
								<?php foreach ($hw->attachments as $att): ?>
								<a href="<?php echo $att->url ?>" target="_blank">
									<span class="icon">
										<?php if ($att->favicon): ?>
										<img src="<?php echo $att->favicon ?>" data-toggle="tooltip" alt="[A]" title="<?php echo $att->title ? $att->title : $att->url ?>">
										<?php else: ?>
										<i class="icon-file" data-toggle="tooltip" title="<?php echo $att->title ? $att->title : $att->url ?>"></i>
										<?php endif; ?>
									</span>
								</a>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
						</td>
						<td class="text-center">
							<i class="ion-android-checkbox btn-confirm btn-confirm-homework" data-homework-id="<?php echo $hw->homework_id ?>"></i>
						</td>
					</tr>
					<?php endforeach; $i++;
					endforeach; ?>
				</tbody>
			</table>
		</div>
	</div> <!-- #homework-popover -->

	<div id="popover-rate" style="display: none">
		<div style="display: none;" class="todo-rating">
			<input type="radio" id="star5" value="5" /><label class = "full" for="star5" title="5 stars"></label>
			<input type="radio" id="star4" value="4" /><label class = "full" for="star4" title="4 stars"></label>
			<input type="radio" id="star3" value="3" /><label class = "full" for="star3" title="3 stars"></label>
			<input type="radio" id="star2" value="2" /><label class = "full" for="star2" title="2 stars"></label>
			<input type="radio" id="star1" value="1" /><label class = "full" for="star1" title="1 star"></label>
		</div>
		<div id="rate-content" class="mb-popover-content">
			<table class="table">
				<thead>
					<tr>
						<th>Key</th>
						<th>ID</th>
						<th>Meeting</th>
						<th class="text-center">Date</th>
						<th class="text-center">Time</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$i = 1; 
					foreach ($my_todo['evaluates'] as $meeting_id => $meeting): 
						$scheduled_end_time = date_create_from_format('Y-m-d H:i:s', $meeting->scheduled_start_time);
						$scheduled_end_time->modify('+' . $meeting->in . ' ' . $meeting->in_type);

						$scheduled_start_date = display_time($meeting->scheduled_start_time, null, 'l jS F');

						$scheduled_start_time = display_time($meeting->scheduled_start_time, null, 'H:ia');
						$scheduled_end_time = display_time($scheduled_end_time->format('Y-m-d H:i:s'), null, 'H:ia');
					?>
					<tr class='parent'>
						<td><?php echo $meeting->meeting_key ?></td>
						<td><?php echo $i ?></td>
						<td><?php echo $meeting->name ?></td>
						<td class="text-center"><?php echo $scheduled_start_date ?></td>
						<td class="text-center"><?php echo $scheduled_start_time . ' - ' . $scheduled_end_time ?></td>
						<td><!--<i class="indicator glyphicon glyphicon-chevron-up pull-right"></i>--></td>
					</tr>
					<tr class='child-head'>
						<th></th>
						<th></th>
						<th>Evaluate</th>
						<th class="text-center">Type</th>
						<th class="text-center">Rate</th>
						<th class="text-center">Confirm</th>
					</tr>
					<?php $j = 1; foreach ($meeting->evaluates as $evaluate): if ($meeting->owner_id == $current_user->user_id && $evaluate->evaluate_mode == 'meeting') continue; ?>
					<tr class='child <?php echo $evaluate->evaluate_mode; echo iif ( ! $evaluate->is_read, ' new') ?>' 
					data-mode="<?php echo $evaluate->evaluate_mode ?>"
					<?php
						if ($evaluate->evaluate_mode == 'agenda') {
							echo 'data-id="' . $evaluate->agenda_id . '"';
						}
						if ($evaluate->evaluate_mode == 'user') {
							echo 'data-id="' . $evaluate->user_id . '" data-meeting-id="'. $evaluate->meeting_id .'"';
						}
						if ($evaluate->evaluate_mode == 'homework') {
							echo 'data-id="' . $evaluate->homework_id . '"';
						}
						if ($evaluate->evaluate_mode == 'meeting') {
							echo 'data-id="' . $evaluate->meeting_id . '"';
						}
					?>
					>
						<td>
							<?php if ( ! $evaluate->is_read): ?>
							<span class="badge badge-warning badge-bordered badge-rate-new">new</span>
							<?php endif; ?>
						</td>
						<td><?php echo $i .'.'. $j++ ?></td>
						<td>
							<?php
							if ($evaluate->evaluate_mode == 'user') {
								echo '<div class="user-wrapper">' . display_user($evaluate->email, $evaluate->first_name, $evaluate->last_name, $evaluate->avatar) . '</div>';
							} elseif ($evaluate->evaluate_mode == 'homework') {
								echo $evaluate->name;
							} elseif ($evaluate->evaluate_mode == 'agenda') {
								echo $evaluate->name;
							} else {
								echo $meeting->name;
							}
							?>
						</td>
						<td class="text-center"><?php echo $evaluate->evaluate_mode ?></td>
						<td class="text-center data" data-url="<?php echo site_url('meeting/dashboard_evaluate/' . $evaluate->evaluate_mode) ?>" data-meeting-id="<?php echo $evaluate->meeting_id ?>"
						<?php
						if ($evaluate->evaluate_mode == 'agenda') {
							echo 'data-id="' . $evaluate->agenda_id . '"';
						}
						if ($evaluate->evaluate_mode == 'user') {
							echo 'data-id="' . $evaluate->user_id . '"';
						}
						if ($evaluate->evaluate_mode == 'homework') {
							echo 'data-id="' . $evaluate->homework_id . '"';
						}
						if ($evaluate->evaluate_mode == 'meeting') {
							echo 'data-id="' . $evaluate->meeting_id . '"';
						}
						?>>
							<div class="todo-rating-wrapper">
								<div class="todo-rating">
									<input type="radio" id="star5" value="5" /><label class = "full" for="star5" title="5 stars"></label>
									<input type="radio" id="star4" value="4" /><label class = "full" for="star4" title="4 stars"></label>
									<input type="radio" id="star3" value="3" /><label class = "full" for="star3" title="3 stars"></label>
									<input type="radio" id="star2" value="2" /><label class = "full" for="star2" title="2 stars"></label>
									<input type="radio" id="star1" value="1" /><label class = "full" for="star1" title="1 star"></label>
								</div>
							</div>
						</td>
						<td class="text-center">
							<i class="ion-android-checkbox btn-confirm submit"></i>
						</td>
					</tr>
					<?php endforeach; $i++;
					endforeach; ?>
				</tbody>
			</table>
		</div>
	</div> <!-- #popover-rate -->

	<div id="popover-today-meetings" style="display: none">
		<div id="upcoming-popover-content" class="mb-popover-content">
			<table class="table">
				<thead>
					<tr>
						<th>Key</th>
						<th>ID</th>
						<th>Meeting</th>
						<th colspan="2" class="text-center">Scheduled start time</th>
						<th colspan="2" class="text-center">Duration</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php $i = 0; foreach ($my_todo['today_meetings'] as $meeting) : $i++ ?>
					<tr data-meeting-id="<?php echo $meeting->meeting_id ?>" class="<?php if (! $meeting->is_read) echo 'upcoming-new' ?>">
						<td><?php echo $meeting->meeting_key ?></td>
						<td><?php echo $i ?></td>
						<td>
							<a href="<?php echo site_url('meeting/' . $meeting->meeting_key) ?>" target="_blank" style="margin-right: 10px"><?php echo $meeting->name ?></a>
							<?php if (! $meeting->is_read) : ?>
							<span class="badge badge-warning badge-bordered badge-todo-new">new</span>
							<?php endif ?>
						</td>
						<td><?php echo display_time($meeting->scheduled_start_time) ?></td>
						<td><?php echo relative_time(strtotime($meeting->scheduled_start_time)) ?></td>
						<td><?php echo $meeting->in ?></td>
						<td><?php echo $meeting->in_type ?></td>
						<td><span class="msg-tag label label-bordered label-<?php echo $meeting->status ?>"><?php echo $meeting->status ?></span></td>
						<td>
						<?php if ($meeting->status == 'open'): ?>
							<a href="<?php echo site_url('meeting/' . $meeting->meeting_key . '#monitor' ) ?>" target="_blank" class="an-btn an-btn-primary-transparent an-btn-small <?php echo ($meeting->owner_id == $current_user->user_id ? '' : ' hidden')?>">
								<?php e(lang('st_set_up')); ?>
							</a>
						<?php elseif ($meeting->status == 'ready' || $meeting->status == 'inprogress'): ?>
							<a href="<?php echo site_url('meeting/' . $meeting->meeting_key . '#monitor' ) ?>" target="_blank" class="an-btn an-btn-primary-transparent an-btn-small <?php echo ($meeting->owner_id == $current_user->user_id ? '' : ' hidden')?>">
								<?php e(lang('st_monitor')); ?>
							</a>
							<a href="<?php echo site_url('meeting/' . $meeting->meeting_key . '#monitor' ) ?>" target="_blank" class="an-btn an-btn-primary-transparent an-btn-small <?php echo ($meeting->owner_id != $current_user->user_id && $meeting->status == 'inprogress' ? '' : ' hidden')?>">
								<?php e(lang('st_join')); ?>
							</a>
						<?php endif; ?>

						<?php if ($meeting->manage_state == 'decide' && $meeting->owner_id == $current_user->user_id): ?>
							<a href="<?php echo site_url('meeting/' . $meeting->meeting_key . '#decide' ) ?>" target="_blank" class="an-btn an-btn-primary-transparent an-btn-small">
								<?php echo lang('st_decider')?>
							</a>
						<?php endif; ?>

						<?php if ((($meeting->manage_state == 'evaluate' || $meeting->manage_state == 'decide' || $meeting->manage_state == 'done') && $is_evaluated($meeting->meeting_id) === false) && $meeting->status != 'finished') : ?>
							<a href="<?php echo site_url('meeting/' . $meeting->meeting_key . '#evaluate' ) ?>" target="_blank" class="an-btn an-btn-primary-transparent an-btn-small">
								<?php echo lang('st_evaluator')?>
							</a>
						<?php endif; ?>
						</td>
					</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</div> <!-- #template -->

<script type="text/vit" id="popover-project">
	<div class="project-header">
		<div class='project-header-content'>
			<h4>
				<a href="<?php echo site_url('project/')?>{{:cost_code}}"
				class="project-title"
				data-toggle="manual"
				data-title="<?php echo lang('pj_edit_project_name') ?>"
				data-pk="{{:project_id}}" 
				data-name="name"
				data-mode="inline"
				data-inputclass="edit-title"
				data-url="<?php echo site_url('project/ajax_edit/') ?>{{:project_id}}" >
					{{:name}}
				</a> 
				<a href="<?php echo site_url('project/')?>{{:cost_code}}">
				<span>[{{:cost_code}}]</span>
				</a>
				{{if is_unspecified_project == 0}}
				<a href="#" class="enable-edit-title" data-target="#project-{{:project_id}}">
					<i class="ion-edit"></i>
				</a>
				{{/if}}
			</h4>
			<p {{if is_unspecified_project == 1}} style="visibility: hidden" {{/if}}>{{:owned_by_x}}</p>
		</div>

		<div class="pull-right">
			{{if has_permission_project_view_all && type == "other"}}
			<button class="an-btn an-btn-primary-transparent an-btn-small btn-join-project" 
				data-lang-joined="<?php echo lang('db_joined') ?>"
				data-project-id="{{:project_id}}">
				<?php echo lang('db_join_project') ?>
			</button>
			{{/if}}

			{{if has_permission_project_edit}}
			<a 	href="<?php echo site_url('meeting/create/') ?>{{:cost_code}}"
				class="an-btn an-btn-primary-transparent an-btn-small btn-create-meeting mb-open-modal"
				data-modal-id="create-meeting">
				<i class="ion-android-add"></i> 
				<?php echo lang('st_new_meeting') ?>
			</a>
			{{/if}}
		</div>
	</div>

	<div class="mb-popover-content" data-project-id="{{:project_id}}">
		<div class="project-body order-1">
			<div class="panel panel-default panel-overview">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#overview-body" role="button" data-toggle="collapse">
							<?php echo lang('db_overview') ?>
						</a>
					</h4>
				</div>
				<div id="overview-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="panel-body">
						<div class="row number-container">
							<div class="col-md-3">
								<i class="ion-android-people"></i>
								<?php echo lang('db_meeting') ?><br/>
								<b class='number'>{{:no_of_meeting}}</b>
							</div>
							<div class="col-md-3 team-wrapper">
								<i class="ion-android-people"></i>
								<?php echo lang('db_team') ?><br/>
								<b class='number'>{{:team}}</b>
							</div>
							<div class="col-md-3 time-wrapper">
								<i class="ion-ios-alarm-outline"></i>
								<?php echo lang('db_time') ?><br/>
								<button class="btn btn-default btn-time dropdown-toggle" data-toggle="dropdown" style="padding: 2px">
									<b class='number'>{{:~round(total_used.time, 10)}}</b>
									<span class='text'><?php echo lang('db_minutes') ?></span>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" data-minute="{{:total_used.time}}">
									<li><a href="#" data-option="minute"><?php echo lang('db_minutes') ?></a></li>
									<li><a href="#" data-option="hour"><?php echo lang('db_hours') ?></a></li>
									<li><a href="#" data-option="day"><?php echo lang('db_days') ?></a></li>
								</ul>
							</div>
							<div class="col-md-3">
								<i class="ion-android-people"></i>
								<?php echo lang('db_points') ?><br/>
								<b class='number'>{{:~round(total_used.point, 10)}}</b>
							</div>
						</div>
					</div>
				</div>
			</div> <!-- Overview -->

			<div class="panel panel-default panel-next-meeting">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#next-meeting-body" role="button" data-toggle="collapse">
							<?php echo lang('db_next_meeting') ?>
						</a>
					</h4>
				</div>
				<div id="next-meeting-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="an-user-lists">
						<div class="list-title">
							<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
							<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
							<h6 class="basis-30"><?php e(lang('pj_scheduled_start_time')) ?></h6>
							<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
						</div>

						<div class="an-lists-body">
						{{if next_meeting}}
							<div class="list-user-single">
								<div class="list-date number basis-30">
									<a href="<?php echo site_url("/meeting/") ?>{{:next_meeting.meeting_key}}">
										{{:next_meeting.meeting_key}}
									</a>
								</div>
								<div class="list-name basis-50">
									<a href="<?php echo site_url("/meeting/") ?>{{:next_meeting.meeting_key}}">
										{{:next_meeting.name}}
									</a>
								</div>
								<div class="list-date number basis-30">
									{{:next_meeting.scheduled_start_time}}
								</div>
								<div class="list-action basis-20">
									<span class="msg-tag label label-bordered label-{{:next_meeting.status}}">
										{{:next_meeting.lang_status}}
									</span>
								</div>
							</div> <!-- end .USER-LIST-SINGLE -->
						{{else}}
							{{include tmpl="#emptyMeetingList"/}}
						{{/if}}
						</div> <!-- end .AN-LISTS-BODY -->
					</div>
				</div>
			</div> <!-- Next Meeting -->

			<div class="panel panel-default panel-unscheduled-meeting">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#unscheduled-meeting-body" role="button" data-toggle="collapse">
							<?php echo lang('db_unscheduled_meeting') ?>
						</a>
					</h4>
				</div>
				<div id="unscheduled-meeting-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="an-user-lists">
						<div class="list-title">
							<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
							<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
							<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
						</div>

						<div class="an-lists-body">
						{{if unscheduled_meetings}}
							{{for unscheduled_meetings tmpl="#unscheduledSingle" /}}
						{{else}}
							<div id="no-meeting" class="list-user-single">
								<div class="list-text basis-30">
								</div>
								<div class="list-date email approve basis-40">
									<?php e(lang('pj_no_meeting')) ?>
								</div>
								<div class="list-text basis-30">
								</div>
							</div>
						{{/if}}
						</div>
					</div>
				</div>
			</div> <!-- Unscheduled Meeting -->

			<div class="panel panel-default panel-scheduled-meeting">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#scheduled-meeting-body" role="button" data-toggle="collapse">
							<?php echo lang('db_scheduled_meeting') ?>
						</a>
					</h4>
				</div>
				<div id="scheduled-meeting-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="an-user-lists">
						<div class="list-title">
							<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
							<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
							<h6 class="basis-30"><?php e(lang('pj_scheduled_start_time')) ?></h6>
							<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
						</div>

						<div class="an-lists-body">
						{{if scheduled_meetings}}
							{{for scheduled_meetings}}
								<div class="list-user-single">
									<div class="list-date number basis-30">
										<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:meeting_key}}</a>
									</div>
									<div class="list-name basis-50">
										<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:name}}</a>
									</div>
									<div class="list-date number basis-30">
										{{:scheduled_start_time}}
									</div>
									<div class="list-action basis-20">
										<span class="msg-tag label label-bordered label-{{:status}}">{{:lang_status}}</span>
									</div>
								</div> <!-- end .USER-LIST-SINGLE -->
							{{/for}}
						{{else}}
							<div id="no-meeting" class="list-user-single">
								<div class="list-text basis-30">
								</div>
								<div class="list-date email approve basis-40">
									<?php e(lang('pj_no_meeting')) ?>
								</div>
								<div class="list-text basis-30">
								</div>
							</div>
						{{/if}}
						</div>
					</div>
				</div>
			</div> <!-- Scheduled Meeting -->

			<div class="panel panel-default panel-completed-meeting">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#completed-meeting-body" role="button" data-toggle="collapse">
							<?php echo lang('db_completed_meeting') ?>
						</a>
					</h4>
				</div>
				<div id="completed-meeting-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="an-user-lists">
						<div class="list-title">
							<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
							<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
							<h6 class="basis-30"><?php e(lang('pj_scheduled_start_time')) ?></h6>
							<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
						</div>

						<div class="an-lists-body">
						{{if completed_meetings}}
							{{for completed_meetings}}
								<div class="list-user-single" data-meeting-id="{{:meeting_id}}" data-meeting-key="{{:meeting_key}}">
									<div class="list-date number basis-30">
										<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:meeting_key}}</a>
									</div>
									<div class="list-name basis-50">
										<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:name}}</a>
									</div>
									<div class="list-date number basis-30">
										{{:scheduled_start_time}}
									</div>
									<div class="list-action basis-20">
										<span class="msg-tag label label-bordered label-{{:status}}">{{:lang_status}}</span>
									</div>
								</div> <!-- end .USER-LIST-SINGLE -->
							{{/for}}
						{{else}}
							<div id="no-meeting" class="list-user-single">
								<div class="list-text basis-30">
								</div>
								<div class="list-date email approve basis-40">
									<?php e(lang('pj_no_meeting')) ?>
								</div>
								<div class="list-text basis-30">
								</div>
							</div>
						{{/if}}
						</div>
					</div>
				</div>
			</div> <!-- Completed Meeting -->
		</div>

		<div class="project-body order-2">
			<div class="panel panel-default panel-overview">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#progress-body" role="button" data-toggle="collapse">
							<?php echo lang('db_project_progress') ?>
						</a>
					</h4>
				</div>
				<div id="progress-body" class="panel-collapse collapse in" role="tabpanel">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-8 col-xs-12">
								<div class="progress-container">
									<div>
										<b>
											<i class="ion-ios-people"></i>
											<?php echo lang('db_meetings') ?>
										</b>

										<span class="pull-right">
											{{:used_meeting}}/{{:no_of_meeting}}
											<?php echo lang('db_meetings') ?>
										</span>
									</div>
								
									<div class="progress">
										<div class="progress-bar progress-bar-success" 
											style="width: {{:used_meeting / no_of_meeting * 100 }}%;">
										</div>
									</div>

									<div class="row text-center">
										<div class="col-md-4">
											<?php echo lang('db_total') ?><br/>
											<b>{{:no_of_meeting}}</b>
										</div>
										<div class="col-md-4">
											<?php echo lang('db_pending') ?><br/>
											<b>{{:pending_meeting}}</b>
										</div>
									</div>
								</div>
								<div class="progress-container">
									<div>
										<b>
											<i class="ion-ios-star"></i>
											<?php echo lang('db_star_rating') ?>
										</b>

										<span class="pull-right">
											{{:rated_stars}}/{{:total_stars}} ({{:~round(rated_stars / total_stars * 100, 10)}}%)
										</span>
									</div>
									<div class="progress">
										<div class="progress-bar progress-bar-success" style="width: {{:~round(rated_stars / total_stars * 100, 10)}}%;">
										</div>
									</div>

									<div class="row text-center">
										<div class="col-md-4">
											<?php echo lang('db_total') ?><br/>
											<b>{{:total_stars}}</b>
										</div>
										<div class="col-md-4">
											<?php echo lang('db_rated') ?><br/>
											<b>{{:rated_stars}}</b>
										</div>
										<div class="col-md-4">
											<?php echo lang('db_unrated') ?><br/>
											<b>{{:total_stars - rated_stars}}</b>
										</div>
									</div>
								</div>

								<div class="progress-container">
									<div>
										<b>
											<i class="ion-ios-people"></i>
											<?php echo lang('db_project_pts') ?>
										</b>

										<span class="pull-right">
											{{:~round(total_used.point, 10)}}/{{:allowed_point}} ({{:~round(total_used.point / allowed_point * 100, 10)}}%)
										</span>
									</div>
									<div class="progress">
										<div class="progress-bar progress-bar-success" style="width: {{:~round(total_used.point / allowed_point * 100, 10)}}%;">
										</div>
									</div>

									<div class="row text-center">
										<div class="col-md-4">
											<?php echo lang('db_allowed_pts') ?><br/>
											<b>{{:allowed_point}}</b>
										</div>
										<div class="col-md-4">
											<?php echo lang('db_logged_pts') ?><br/>
											<b>{{:~parseFloat(total_used.point.toFixed(1))}}</b>
										</div>
										<div class="col-md-4">
											<?php echo lang('db_unused_pts') ?><br/>
											<b>{{:allowed_point - ~parseFloat(total_used.point.toFixed(1))}}</b>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4 col-xs-12">
								<canvas id="pie-chart" width="400" height="400">
								</canvas>
							</div>
						</div> <!-- .row -->
					</div>
				</div>
			</div> <!-- Project Progress -->
		</div>

		<div class="project-body order-3">
			<div class="panel panel-default">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#team-body" role="button" data-toggle="collapse">
							<?php echo lang('db_team') ?>
						</a>
						<button class="an-btn an-btn-icon small primary btn-add-project-member pull-right mb-open-modal" data-modal-id="add-project-member-modal" data-url="<?php echo site_url('project/add_project_member/') ?>{{:project_id}}"><i class="ion-android-person-add"></i></button>
					</h4>
				</div>
				<div id="team-body" class="panel-collapse collapse in" role="tabpanel">
					{{for members}}
					<div class="member">
						<div class="row">
							<div class="col-md-11">
								<span class="avatar" style="background-image: url('{{:avatar_url}}')"></span>
								<span class="info">
									<a href="#"><b>{{:full_name}}</b></a><br>
									<span class="text-info">
									<?php echo lang('db_project_pts') ?> {{:pts.toFixed(1)}} &nbsp;
									<i class="ion-ios-star"></i> {{:rated_stars}}/{{:total_stars}} &nbsp;
									<?php echo lang('db_avg') ?> 
										{{:~countingStars( (rated_stars / total_stars * 5).toFixed(0) )}}
										{{:~countingStars( total_stars > 0 ? 5 - (rated_stars / total_stars * 5).toFixed(0) : 5, "ion-ios-star-outline")}}
										{{: total_stars > 0 ? (rated_stars / total_stars * 5).toFixed(0) : ''}}
									</span>
								</span>
							</div>
							<div class="col-md-1" style="padding: 10px;">
								<button class="an-btn an-btn-icon small muted danger btn-remove-member" 
									data-user-id="{{:user_id}}"
									data-full-name="{{:full_name}}">
									<i class="icon-trash"></i>
								</button>
							</div>
						</div>
					</div> <!-- .member -->
					{{/for}}
				</div>
			</div> 
		</div> <!-- Team members -->

		<div class="project-body order-4">
			<div class="panel panel-default">
				<div class="panel-heading" role="tab">
					<h4 class="panel-title">
						<a href="#stats-body" role="button" data-toggle="collapse">
							<?php echo lang('db_statistics') ?>
						</a>
					</h4>
				</div>
				<div id="stats-body" class="panel-collapse collapse in" role="tabpanel">
					<canvas id="stats-chart" width="100%" height="400"></canvas>
				</div>
			</div> 
		</div> <!-- Team members -->
	</div>
</script>

<script id="emptyMeetingList" type="text/vit">
	<div class="list-user-single">
		<div class="list-text basis-30">
		</div>
		<div class="list-date email approve basis-40">
			<?php e(lang('pj_no_meeting')) ?>
		</div>
		<div class="list-text basis-30">
		</div>
	</div>
</script>

<script id="unscheduledSingle" type="text/vit">
	<div class="list-user-single">
		<div class="list-date number basis-30">
			<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:meeting_key}}</a>
		</div>
		<div class="list-name basis-50">
			<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:name}}</a>
		</div>
		<div class="list-action basis-50">
			<span class="msg-tag label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</div>
	</div> <!-- end .USER-LIST-SINGLE -->
</script>

<script id="scheduledSingle" type="text/bao">
	<div class="list-user-single">
		<div class="list-date number basis-30">
			<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:meeting_key}}</a>
		</div>
		<div class="list-name basis-50">
			<a href="<?php echo "/meeting/" ?>{{:meeting_key}}">{{:name}}</a>
		</div>
		<div class="list-date number basis-30">
			{{:display_scheduled_start_time}}
		</div>
		<div class="list-action basis-20">
			<span class="msg-tag label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</div>
	</div> <!-- end .USER-LIST-SINGLE -->
</script>

<script id="projectTeamSingle" type="text/bao">
	<div class="member">
		<div class="row">
			<div class="col-md-11">
				<span class="avatar" style="background-image: url('{{:avatar_url}}')"></span>
				<span class="info">
					<a href="#"><b>{{:full_name}}</b></a><br>
					<span class="text-info">
					<?php echo lang('db_project_pts') ?> {{:pts.toFixed(1)}} &nbsp;
					<i class="ion-ios-star"></i> {{:rated_stars}}/{{:total_stars}} &nbsp;
					<?php echo lang('db_avg') ?> 
						{{:~countingStars( (rated_stars / total_stars * 5).toFixed(0) )}}
						{{:~countingStars( total_stars > 0 ? 5 - (rated_stars / total_stars * 5).toFixed(0) : 5, "ion-ios-star-outline")}}
						{{: total_stars > 0 ? (rated_stars / total_stars * 5).toFixed(0) : ''}}
					</span>
				</span>
			</div>
			<div class="col-md-1" style="padding: 10px;">
				<button class="an-btn an-btn-icon small muted danger btn-remove-member" 
					data-user-id="{{:user_id}}"
					data-full-name="{{:full_name}}">
					<i class="icon-trash"></i>
				</button>
			</div>
		</div>
	</div> <!-- .member -->
</script>

<div class="hidden">
	<textarea name="recurring">RRULE:FREQ=DAILY;INTERVAL=5;COUNT=100</textarea>
</div>