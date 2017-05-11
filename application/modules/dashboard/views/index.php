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
							<div class="list-date basis-40">
								<?php echo anchor(site_url('step/' . $step->step_key), $step->name); ?>
							</div>
							<div class="list-text basis-20">
								<?php echo display_user($step->email, $step->first_name, $step->last_name, $step->avatar); ?>
							</div>
							<div class="list-state basis-10">
								<span class="msg-tag <?php echo $step_status_labels[$step->status] ?>">
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
	<div class="col-xs-12">
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
							<div class="list-name basis-10">
								<?php e($project->project_id)?>
							</div>
							<div class="list-date basis-30">
								<?php echo anchor(site_url() . 'project/' . $project->cost_code, $project->name); ?>
							</div>
							<div class="list-date basis-20">
								<?php e($project->cost_code)?>
							</div>
							<div class="list-text basis-30">
								<?php echo display_user($project->email, $project->first_name, $project->last_name, $project->avatar); ?>
							</div>
							<div class="list-state basis-10">
								<span class="msg-tag <?php echo $project_status_labels[$project->status] ?>">
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


<!-- Modal -->
<div class="modal fade" id="bigModal" tabindex="-1" role="dialog" aria-labelledby="bigModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal fade" id="inviteModal" tabindex="-1" role="dialog" aria-labelledby="bigModalLabel">
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

<script>
	var CREATE_PROJECT_URL = '<?php echo site_url('project/create')?>';
	var INVITE_USER_URL = '<?php echo site_url('admin/team/invite')?>';
</script>