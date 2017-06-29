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
foreach ($projects as $project) {
	if ($project->is_read == 0) {
		$has_new_project = true;
		break;
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
			<li class="an-nav-item">
				<a id="my-todo" class="js-show-child-nav nav-open" href="#">
					<i class="ion-ios-copy-outline"></i>
					<span class="nav-title">My To Do
					<?php if ($has_new_homework):?>
					<span class="badge badge-warning badge-bordered badge-todo-new">new</span>
					<?php endif; ?>
					<span class="count"><?php echo $my_todo['homeworks_count'] + count($my_todo['evaluates']) ?></span>
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
							<span class="badge badge-primary pull-right homework-counter"><?php echo $my_todo['homeworks_count'] ?></span>
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
							<span class="badge badge-primary pull-right"><?php echo count($my_todo['evaluates']) ?></span>
						</a>
					</li>
				</ul>
			</li>

			<li class="an-nav-item">
				<a id="my-project" class="js-show-child-nav" href="#">
					<i class="ion-ios-briefcase-outline"></i>
					<span class="nav-title">My Projects

					<?php if ($has_new_project):?>
					<span class="badge badge-warning badge-bordered badge-new">new</span>
					<?php endif; ?>
					<?php if (count($projects) > 0): ?>
					<span class="count"><?php e(count($projects)) ?></span>
					<?php endif; ?>
					</span>
				</a>

				<ul class="an-child-nav js-open-nav" style="display: none;">
					<?php foreach ($projects AS $project): ?>
					<li>
						<a href="<?php echo site_url('project/' . $project->cost_code)?>" class='mb-popover-project <?php if ( !$project->is_read) echo 'new' ?>' 
							data-project-id="<?php echo $project->project_id ?>" 
							data-toggle="popover" 
							data-placement="right">
							<?php echo ($project->name . " <b>[{$project->cost_code}]</b>") ?>

							<?php if ( ! $project->is_read): ?>
							<span class="badge badge-warning badge-bordered badge-new">new</span>
							<?php endif; ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</li>
		</ul> <!-- end .AN-MAIN-NAV -->
	</div> <!-- /.an-sidebar-nav -->

	<div class="an-dashboard-content">
		<header class="an-header wow fadeInDown">
			<div class="an-topbar-left-part">
			</div> <!-- end .AN-TOPBAR-LEFT-PART -->

			<div class="an-topbar-right-part">
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

				<div class="an-settings">
					<div class="btn-group an-notifications-dropown settings">
						<button type="button" class="an-btn an-btn-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ion-ios-gear-outline"></i>
						</button>
						<div class="dropdown-menu">
							<p class="an-info-count">Settings</p>
							<ul class="an-settings-list">
								<li><a href="<?php echo site_url('admin/settings'); ?>"><i class="ion-ios-settings"></i>Preferences</a></li>
								<li><a href="<?php echo site_url('admin/team'); ?>"><i class="ion-ios-people-outline"></i>Team</a></li>
								<li><a href="<?php echo site_url('admin/invites'); ?>"><i class="ion-ios-personadd-outline"></i>Invitations</a></li>
								<li><a href="<?php echo site_url('admin/billing'); ?>"><i class="ion-social-usd-outline"></i>Billing</a></li>
								<li><a href="<?php echo site_url('admin/auth'); ?>"><i class="ion-ios-locked-outline"></i>Authentication</a></li>
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

		<div class="an-page-content">
			<?php // @ BaoDG : Add Calendar here ?>
			<div id="fullcalendar">
			</div>
		</div> <!-- end .AN-PAGE-CONTENT -->

	</div>
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
						<i class="ion-android-checkbox btn-confirm-homework" data-homework-id="<?php echo $hw->homework_id ?>"></i>
					</td>
				</tr>
				<?php endforeach; $i++;
				endforeach; ?>
			</tbody>
		</table>
	</div>
</div>

<div id="popover-rate" style="display: none">
	<div id="rate-content" class="mb-popover-content">
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
					<th></th>
					<th class="text-center">Confirm</th>
				</tr>
				<?php $j = 1; foreach ($homework as $hw):?>
				<tr data-homework-id="<?php echo $hw->homework_id ?>" class='child'>
					<td></td>
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
						<i class="ion-android-checkbox btn-confirm-homework" data-homework-id="<?php echo $hw->homework_id ?>"></i>
					</td>
				</tr>
				<?php endforeach; $i++;
				endforeach; ?>
			</tbody>
		</table>
	</div>
</div> <!-- #popover-rate -->

<?php foreach ($projects as $project): ?>
<div id="popover-project-<?php echo $project->project_id ?>" style="display: none">
	<div class="project-header">
			<h4>
				<a href="#" class='mb-editable'
				data-title="<?php echo lang('pj_edit_project_name') ?>"
				data-pk="<?php echo $project->project_id ?>" 
				data-name="name"
				data-mode="inline"
				data-inputclass="edit-title"
				data-url="<?php echo site_url('project/ajax_edit') ?>" >
					<?php echo $project->name ?>
				</a> 
				<a href="<?php echo site_url('project/' . $project->cost_code)?>">
				<span>[<?php echo $project->cost_code ?>]</span>
				</a>
			</h4>
		<?php echo sprintf(lang('db_owned_by_x'), $project->first_name) ?>
	</div>

	<div class="mb-popover-content">
		<div class="project-body">
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
								<b class='number'><?php echo $project->no_of_meeting ?></b>
							</div>
							<div class="col-md-3">
								<i class="ion-android-people"></i>
								<?php echo lang('db_team') ?><br/>
								<b class='number'><?php echo $project->member_number ?></b>
							</div>
							<div class="col-md-3 time-wrapper">
								<i class="ion-ios-alarm-outline"></i>
								<?php echo lang('db_time') ?><br/>
								<button class="btn btn-default btn-time dropdown-toggle" data-toggle="dropdown" style="padding: 2px">
									<b class='number'><?php echo round($project->total_used['time'], 2) ?></b>
									<span class='text'><?php echo lang('db_minutes') ?></span>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" data-minute="<?php echo round($project->total_used['time'], 2) ?>">
									<li><a href="#" data-option="minute"><?php echo lang('db_minutes') ?></a></li>
									<li><a href="#" data-option="hour"><?php echo lang('db_hours') ?></a></li>
									<li><a href="#" data-option="day"><?php echo lang('db_days') ?></a></li>
								</ul>
							</div>
							<div class="col-md-3">
								<i class="ion-android-people"></i>
								<?php echo lang('db_points') ?><br/>
								<b class='number'><?php echo round($project->total_used['point'], 2) ?></b>
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
						<?php if ($project->next_meeting) : $item = $project->next_meeting; ?>
							<div class="list-user-single">
								<div class="list-date number basis-30">
									<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->meeting_key) ?></a>
								</div>
								<div class="list-name basis-50">
									<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->name) ?></a>
								</div>
								<div class="list-date number basis-30">
									<?php echo display_time($item->scheduled_start_time) ?>
								</div>
								<div class="list-action basis-20">
									<span class="msg-tag label label-bordered label-<?php echo $item->status ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
								</div>
							</div> <!-- end .USER-LIST-SINGLE -->
						<?php else : ?>
							<div id="no-meeting" class="list-user-single">
								<div class="list-text basis-30">
								</div>
								<div class="list-date email approve basis-40">
									<?php e(lang('pj_no_meeting')) ?>
								</div>
								<div class="list-text basis-30">
								</div>
							</div>
						<?php endif ?>
						</div> <!-- end .AN-LISTS-BODY -->
					</div>
				</div>
			</div> <!-- Next Meeting -->

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
						<?php if ($project->scheduled_meetings && count($project->scheduled_meetings)) : 
								foreach ($project->scheduled_meetings as $item) : ?>
								<div class="list-user-single">
									<div class="list-date number basis-30">
										<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->meeting_key) ?></a>
									</div>
									<div class="list-name basis-50">
										<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->name) ?></a>
									</div>
									<div class="list-date number basis-30">
										<?php echo display_time($item->scheduled_start_time) ?>
									</div>
									<div class="list-action basis-20">
										<span class="msg-tag label label-bordered label-<?php echo $item->status ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
									</div>
								</div> <!-- end .USER-LIST-SINGLE -->
							<?php endforeach; 
						else : ?>
							<div id="no-meeting" class="list-user-single">
								<div class="list-text basis-30">
								</div>
								<div class="list-date email approve basis-40">
									<?php e(lang('pj_no_meeting')) ?>
								</div>
								<div class="list-text basis-30">
								</div>
							</div>
						<?php endif ?>
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
						<?php if ($project->completed_meetings && count($project->completed_meetings)) : 
								foreach ($project->completed_meetings as $item) : ?>
								<div class="list-user-single">
									<div class="list-date number basis-30">
										<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->meeting_key) ?></a>
									</div>
									<div class="list-name basis-50">
										<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->name) ?></a>
									</div>
									<div class="list-date number basis-30">
										<?php echo display_time($item->scheduled_start_time) ?>
									</div>
									<div class="list-action basis-20">
										<span class="msg-tag label label-bordered label-<?php echo $item->status ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
									</div>
								</div> <!-- end .USER-LIST-SINGLE -->
							<?php endforeach; 
						else : ?>
							<div id="no-meeting" class="list-user-single">
								<div class="list-text basis-30">
								</div>
								<div class="list-date email approve basis-40">
									<?php e(lang('pj_no_meeting')) ?>
								</div>
								<div class="list-text basis-30">
								</div>
							</div>
						<?php endif ?>
						</div>
					</div>
				</div>
			</div> <!-- Completed Meeting -->
		</div>
	</div>
</div>
<?php endforeach; ?>
</div> <!-- #template -->