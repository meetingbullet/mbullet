<?php
$is_owner = $step->owner_id == $current_user->user_id;
$scheduled_start_time = null;

if ($step->scheduled_start_time) {
	$scheduled_start_time = strtotime($step->scheduled_start_time);
	$scheduled_end_time = strtotime('+' . $step->in . ' ' . $step->in_type, $scheduled_start_time);

	$scheduled_start_time = date('M d, H:i', $scheduled_start_time);
	$scheduled_end_time = date('M d, H:i', $scheduled_end_time);
}

$scheduled_time = $scheduled_start_time ? $scheduled_start_time . ' - ' . $scheduled_end_time : null;

$task_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
];

$confirmation_status = [
	'closed', 'skipped', 'resolved', 'open_parking_lot', 'closed_parking_lot'
];

$cost_of_time_to_badge = [
	'', // Skip cost_of_time_to_badge[0]
	'default',	// XS
	'info',	// S
	'success',		// M
	'primary',	// L
	'warning',	// XL
];
?>
<div class="step-decider" data-is-owner="<?php echo $is_owner ? 1 : 0 ?>">
	<?php if (IS_AJAX): ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_step_decider'))?></h4>
	</div> <!-- end MODAL-HEADER -->
	<?php endif; ?>

	<?php echo form_open(site_url('step/update_decider/' . $step->step_key), ['class' => 'form-inline form-step-decider an-helper-block']) ?>
		<div class="an-body-topbar">
			<div class="an-page-title">
				<div class="an-bootstrap-custom-tab">
					<h2><?php e($step->name . ' - ' . lang('st_decider'))?></h2>
				</div>
			</div>
			<div class="pull-right">
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->

		<div class="decider-step-container row">
			<div class="col-md-5">
				<div class="an-single-component with-shadow">
					<div class="an-component-body an-helper-block">
						<table class="table table-striped table-step-time">
							<thead>
								<tr>
									<th></th>
									<th><?php e(lang('st_scheduled'))?></th>
									<th><?php e(lang('st_actual'))?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?php e(lang('st_start_time')) ?></td>
									<td><?php echo $scheduled_start_time ?></td>
									<td><?php echo date('M d, H:i', strtotime($step->actual_start_time)) ?></td>
								</tr>
								<tr>
									<td><?php e(lang('st_end_time')) ?></td>
									<td><?php echo $scheduled_end_time ?></td>
									<td><?php echo date('M d, H:i', strtotime($step->actual_end_time)) ?></td>
								</tr>
								<tr>
									<td><?php e(lang('st_elapsed_time')) ?></td>
									<td><?php echo $step->in . ' ' . lang('st_' . $step->in_type) ?></td>
									<td><?php echo round($step->actual_elapsed_time, 2) . ' ' . lang('st_minutes') ?></td>
								</tr>
							</tbody>
						</table>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div>
			</div>

			<div class="col-md-4">
				<div class="an-single-component with-shadow">
					<div class="an-component-header">
						<h6><?php e(lang('st_goal'))?></h6>
					</div>
					<div class="an-component-body an-helper-block">
						<?php echo word_limiter($step->goal, 100)?>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div>
			</div>
			<div class="col-md-3">
				<div class="an-single-component with-shadow">
					<div class="an-component-header">
						<h6><?php e(lang('st_attendees'))?></h6>
					</div>
					<div class="an-component-body an-helper-block">
						<ul class="list-unstyled list-member">
							<?php foreach ($step->members as $user) { ?>
							<li>
								<?php echo display_user($user['email'], $user['first_name'], $user['last_name'], $user['avatar']); ?>

								<span class="badge badge-<?php e($cost_of_time_to_badge[$user['cost_of_time']])?> badge-bordered pull-right"><?php e($user['cost_of_time_name'])?></span>
							</li>
							<?php } ?>
						</ul>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div>
			</div>
		</div>

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_tasks'))?></h6>
			</div>
			<div class="an-component-body an-helper-block">
				<table class="table table-striped table-step-time">
					<thead>
						<tr>
							<th><?php e(lang('st_name'))?></th>
							<th><?php e(lang('st_started_on'))?></th>
							<th><?php e(lang('st_duration'))?></th>
							<th><?php e(lang('st_meeting_status'))?></th>
							<th><?php e(lang('st_confirmation_status'))?></th>
						</tr>
					</thead>
					<tbody>
						<?php if($tasks): foreach ($tasks as $task) : ?>
						<tr id='task-<?php e($task->task_id)?>' data-task-id='<?php e($task->task_id)?>' data-task-status='<?php e($task->status)?>'>
							<td><?php echo anchor(site_url('task/' . $task->task_key), $task->name, ['target' => '_blank'])?></td>
							<td><?php echo $task->started_on ?></td>
							<td><?php echo $task->started_on ?></td>
							<td>
								<span class="<?php e($task_status_labels[$task->status])?>">
									<?php e(lang('st_' . $task->status))?>
								</span>
							</td>
							<td>
								<select name="tasks[<?php e($task->task_key) ?>]" class="confirmation-status an-form-control">
									<option disabled selected value><?php e(lang('st_select_an_option')) ?></option>
									<?php foreach ($confirmation_status as $status) {
										echo "<option value='{$status}'>". lang('st_' . $status) ."</option>";
									} ?>
								</select>
							</td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>

				<label for="note"><?php e(lang('st_notes'))?></label>
		<div class="row">
			<div class="col-md-12">
				<textarea id="note" name="note" rows="6" class="an-form-control note" placeholder="<?php e(lang('st_write_a_note_here')) ?>"></textarea>
				<button class="an-btn an-btn-primary btn-close-out-step"><?php e(lang('st_close_out_step')) ?></button>
			</div>
		</div>

	<?php echo form_close() ?>
</div>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('decider_js', [
			
	], true) . '</script>';
}
?>

<?php if ( ! IS_AJAX): ?>
<!-- Modal -->
<div id="resolve-task" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>
<?php endif; ?>