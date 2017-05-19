<?php
$step_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'finished' => 'label label-success label-bordered',
	'ready' => 'label label-info label-bordered',
];

$project_status_labels = [
	'open' => 'label label-default label-bordered',
	'inactive' => 'label label-warning label-bordered',
	'archive' => 'label label-success label-bordered',
];
?>

<div class="row">
	<!-- Welcome Professor -->
	<div class="col-md-3 col-xs-12">
		<div class="welcome-panel">
			<div class="user-info">
				<div class="avatar" style="background-image: url('<?php echo avatar_url($user->avatar, $user->email, 128) ?>')"></div>
				<div class="text-center">
					<h3><?php echo sprintf(lang('db_welcome_x'), $user->first_name)?></h3>

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

						<?php if ($user->meeting_count !== null): ?>
						<span class="meeting-count">
							<?php e(
								sprintf($user->meeting_count > 1 
										? lang('db_x_meetings') 
										: lang('db_x_meeting')
								, $user->meeting_count)
							) ?>
						</span>
						<?php endif; ?>
					</div>
					<div class="total-points"><?php e(sprintf(lang('db_total_points_x'), round($user->total_point_used, 2) )) ?></div>
				</div> <!-- end .user-info -->

				<div class="action-panel an-helper-block">
					<span class="text-muted"><?php e(lang('db_what_do_you_want_to_do'))?></span>
					<hr>
					<div class="btn-group">
						<button type="button" class="an-btn an-btn-primary block-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ion-ios-plus-outline"></i>
							<?php e(lang('db_create_work')) ?> <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="#">Action</a></li>
							<li><a href="#">Another action</a></li>
							<li><a href="#">Something else here</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="#">Separated link</a></li>
						</ul>
					</div>

					<button class="an-btn an-btn-primary block-icon"><i class="ion-ios-play-outline"></i><?php e(lang('db_do_work')) ?></button><br>
					<button class="an-btn an-btn-default block-icon"><i class="ion-ios-speedometer-outline"></i><?php e(lang('db_review_work')) ?></button><br>
				</div>
			</div> <!-- end .welcome-panel -->
		</div>
	</div> <!-- end Welcome Professor -->

	<!-- Main contents -->
	<div class="col-md-9 col-xs-12">
		<button class="an-btn an-btn-primary" id="create" style="margin-bottom: 30px">Create Project</button>
		<button class="an-btn an-btn-success" id="invite" style="margin-bottom: 30px">Invite User</button>

		<div class="row">
		<?php if (is_array($my_steps) && count($my_steps) > 0) { ?>
			<div class="col-xs-12">
				<div class="an-single-component with-shadow">
					<div class="an-component-header">
						<h6>Imcomming Steps</h6>
						<div class="component-header-right">
						</div>
					</div>
					<div class="an-component-body">
						<div class="an-user-lists">
							<div class="list-title">
								<h6 class="basis-40">Step</h6>
								<h6 class="basis-20">Owner</h6>
								<h6 class="basis-10">Status</h6>
								<h6 class="basis-20">Start in</h6>
								<h6 class="basis-10">Action</h6>
							</div>

							<div class="an-lists-body">
								<?php foreach($my_steps as $step): ?>
								<div class="list-user-single">
									<div class="list-name basis-40">
										<?php echo anchor(site_url('step/' . $step->step_key), $step->name); ?>
									</div>
									<div class="list-text basis-20">
										<?php echo display_user($step->email, $step->first_name, $step->last_name, $step->avatar); ?>
									</div>
									<div class="list-state basis-10">
										<span class="msg-tag label label-bordered label-<?php echo $step->status ?>">
											<?php e($step->status)?>
										</span>
									</div>
									<div class="list-time basis-20">
										<?php
										if ($step->status == 'ready') {
											echo relative_time(strtotime(user_time(strtotime($step->scheduled_start_time))));
										} elseif ($step->status == 'inprogress') {
											echo relative_time(strtotime(user_time(strtotime($step->actual_start_time))));
										}
										?>
									</div>
									<div class="list-action basis-10">
										<?php
										// is owner
										if ($step->owner_id == $current_user->user_id) {
											if ($step->status == 'ready') {
												if ($step->scheduled_start_time <= date('Y-m-d H:i:s')) {
													// start
										?>
										<button class="an-btn an-btn-small an-btn-primary btn-open-step-monitor" data-step-key="<?php e($step->step_key)?>">
											<i class="ion-ios-play"></i> <?php echo lang('st_start'); ?>
										</button>
										<?php
												}
											} elseif ($step->status == 'inprogress') {
										?>
										<button class="an-btn an-btn-small an-btn-primary btn-open-step-monitor" data-step-key="<?php e($step->step_key)?>">
											<i class="ion-android-open"></i> <?php echo lang('st_open'); ?>
										</button>
										<?php
											}
										} else {
											// member only
											if ($step->status == 'inprogress') {
										?>
										<button class="an-btn an-btn-small an-btn-primary btn-open-step-monitor" data-step-key="<?php e($step->step_key)?>">
											<i class="ion-link"></i> <?php echo lang('st_join'); ?>
										</button>
										<?php
											}
										}
										?>
									</div>
								</div> <!-- end .USER-LIST-SINGLE -->
								<?php endforeach; ?>
							</div>
						</div>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div> <!-- end .an-single-component -->
			</div>
		<?php } ?>
			<div id="project-list" class="col-xs-12">
				<div class="an-single-component with-shadow">
					<div class="an-component-header">
						<h6>My Projects</h6>
						<div class="component-header-right">
						</div>
					</div>
					<div class="an-component-body">
						<div class="an-user-lists">
							<div class="list-title">
								<h6 class="basis-10">
									ID
								</h6>
								<h6 class="basis-30">Project Name</h6>
								<h6 class="basis-20">Cost Code</h6>
								<h6 class="basis-30">Owner</h6>
								<h6 class="basis-10">Status</h6>
								<h6 class="basis-20">Created on</h6>
							</div>

							<div class="an-lists-body">
								<?php foreach($projects as $project): ?>
								<div class="list-user-single">
									<div class="list-date basis-10">
										<?php e($project->project_id)?>
									</div>
									<div class="list-name basis-30">
										<?php echo anchor(site_url() . 'project/' . $project->cost_code, $project->name); ?>
									</div>
									<div class="list-date basis-20">
										<?php e($project->cost_code)?>
									</div>
									<div class="list-text basis-30">
										<?php echo display_user($project->email, $project->first_name, $project->last_name, $project->avatar); ?>
									</div>
									<div class="list-state basis-10">
										<span class="msg-tag label label-bordered label-<?php echo $project->status ?>">
											<?php e($project->status)?>
										</span>
									</div>
									<div class="list-action basis-20">
										<?php e(display_time($project->created_on))?>
									</div>
								</div> <!-- end .USER-LIST-SINGLE -->
								<?php endforeach; ?>
							</div>

						</div>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div> <!-- end .an-single-component -->
			</div>

		</div>

	</div>
	<!-- end Main contents -->
</div>



<!-- Modal -->
<div class="modal fade" id="bigModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal fade" id="inviteModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal modal-monitor fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="resolve-task" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="step-decider" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="create-step" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal modal-monitor-evaluator fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal waiting-modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<?php echo '<p class="text-center">' . lang('st_waiting_evaluator') . '</p>' ?>
		</div>
	</div>
</div>

<script>
	var CREATE_PROJECT_URL = '<?php echo site_url('project/create')?>';
	var INVITE_USER_URL = '<?php echo site_url('admin/team/invite')?>';
</script>

<script id="project-row" type="text">
	<div class="list-user-single">
		<div class="list-date basis-10">{{:project_id}}</div>
		<div class="list-name basis-30"><a href="<?php echo site_url('project')?>/{{:cost_code}}">{{:name}}</a></div>
		<div class="list-date basis-20">{{:cost_code}}</div>
		<div class="list-text basis-30">{{:display_user}}</div>
		<div class="list-state basis-10">
			<span class="msg-tag label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</div>
		<div class="list-action basis-20">{{:created_on}}</div>
	</div>
</script>