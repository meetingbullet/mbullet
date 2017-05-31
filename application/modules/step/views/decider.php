<?php
$is_owner = $step->owner_id == $current_user->user_id;
$scheduled_start_time = null;

if ($step->scheduled_start_time) {
	// Fix add StrToTime with Float number
	if ( (int) $step->in !== $step->in ) {
		switch ($step->in_type) {
			case 'weeks':
				$step->in *= 7;
			case 'days':
				$step->in *= 24;
			case 'hours':
				$step->in *= 60;
			case 'minutes':
				$step->in *= 60;
		}
	}

	$scheduled_start_time = strtotime($step->scheduled_start_time);
	$scheduled_end_time = strtotime('+' . $step->in . ' seconds', $scheduled_start_time);
	$step->in = round( $step->in / 60, 2);
	$step->in_type = 'minutes';

	$scheduled_start_time = date('Y-m-d H:i:s', $scheduled_start_time);
	$scheduled_end_time = date('Y-m-d H:i:s', $scheduled_end_time);
}

$scheduled_time = $scheduled_start_time ? $scheduled_start_time . ' - ' . $scheduled_end_time : null;

$agenda_status_labels = [
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
					<h2><?php e($step->name)?></h2>
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
									<td><strong><?php e(lang('st_start_time')) ?></strong></td>
									<td class="text-center"><?php echo display_time($scheduled_start_time) ?></td>
									<td class="text-center"><?php echo display_time($step->actual_start_time) ?></td>
								</tr>
								<tr>
									<td><strong><?php e(lang('st_end_time')) ?></strong></td>
									<td class="text-center"><?php echo display_time($scheduled_end_time) ?></td>
									<td class="text-center"><?php echo display_time($step->actual_end_time) ?></td>
								</tr>
								<tr>
									<td><strong><?php e(lang('st_elapsed_time')) ?></strong></td>
									<td class="text-center"><?php echo timespan(strtotime($step->scheduled_start_time), strtotime($scheduled_end_time) ) ?></td>
									<td class="text-center"><?php echo timespan(strtotime($step->actual_start_time), strtotime($step->actual_end_time)) ?></td>
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
						<?php echo nl2br($step->goal)?>
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

								<span class="badge badge-<?php e($user['cost_of_time'])?> badge-bordered pull-right"><?php e($user['cost_of_time_name'])?></span>
							</li>
							<?php } ?>
						</ul>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div>
			</div>
		</div>

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_agendas'))?></h6>
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
						<?php if($agendas): foreach ($agendas as $agenda) : ?>
						<tr id='agenda-<?php e($agenda->agenda_id)?>' data-agenda-id='<?php e($agenda->agenda_id)?>' data-agenda-status='<?php e($agenda->status)?>'>
							<td><?php echo anchor(site_url('agenda/' . $agenda->agenda_key), $agenda->name, ['target' => '_blank'])?></td>
							<td><?php echo display_time($agenda->started_on) ?></td>
							<td><?php echo timespan(strtotime($agenda->started_on), strtotime($agenda->finished_on)) ?></td>
							<td>
								<span class="label label-bordered label-<?php e($agenda->status)?>">
									<?php e(lang('st_' . $agenda->status))?>
								</span>
							</td>
							<td>
								<select name="agendas[<?php e($agenda->agenda_key) ?>]" class="confirmation-status an-form-control">
									<option disabled selected value><?php e(lang('st_select_an_option')) ?></option>
									<?php foreach ($confirmation_status as $status) {
										echo "<option value='{$status}' ". ($agenda->confirm_status == $status ? ' selected' : '') .">". lang('st_' . $status) ."</option>";
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
		'action_key' => $action_key,
		'step_key' => $step->step_key,
		'step_id' => $step->step_id
	], true) . '</script>';
}
?>

<?php if ( ! IS_AJAX): ?>
<!-- Modal -->
<div id="resolve-agenda" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>
<?php endif; ?>