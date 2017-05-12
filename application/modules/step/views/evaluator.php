<?php
$task_confirm_status_labels = [
	'closed' => 'label label-default label-bordered',
	'skipped' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'open_parking_lot' => 'label label-info label-bordered',
	'close_parking_lot' => 'label label-success label-bordered'
];
$task_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
];
?>
<?php echo form_open('', ['class' => 'form-inline form-ajax form-step-schedule']) ?>
<div style="display:none" class="rating">
	<input type="radio" id="star5" value="5" /><label class = "full" for="star5" title="5 stars"></label>
	<input type="radio" id="star4" value="4" /><label class = "full" for="star4" title="4 stars"></label>
	<input type="radio" id="star3" value="3" /><label class = "full" for="star3" title="3 stars"></label>
	<input type="radio" id="star2" value="2" /><label class = "full" for="star2" title="2 stars"></label>
	<input type="radio" id="star1" value="1" /><label class = "full" for="star1" title="1 star"></label>
</div>
<div class="step-monitor">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_step_monitor'))?></h4>
	</div> <!-- end MODAL-HEADER -->

	<div class="an-body-topbar">
		<div class="an-page-title">
			<div class="an-bootstrap-custom-tab">
				<h2><?php e($step->name . ' - ' . lang('st_evaluation'))?></h2>
			</div>
		</div>
	</div> <!-- end AN-BODY-TOPBAR -->

	<div class="col-md-4">
		<div class="an-single-component">
			<div class="an-component-body an-helper-block">
				<table class="table">
					<thead>
						<tr>
							<th></th>
							<th class="text-center"><?php e(lang('st_scheduled'))?></th>
							<th class="text-center"><?php e(lang('st_actual'))?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><?php echo lang('st_start_time') ?></strong></td>
							<td class="text-center"><?php e($step->scheduled_start_time) ?></td>
							<td class="text-center"><?php e($step->actual_start_time) ?></td>
						</tr>
						<tr>
							<td><strong><?php echo lang('st_end_time') ?></strong></td>
							<td class="text-center"><?php //e($step->scheduled_end_time) ?></td>
							<td class="text-center"><?php e($step->actual_end_time) ?></td>
						</tr>
						<tr>
							<td><strong><?php echo lang('st_elapsed_time') ?></strong></td>
							<td class="text-center"></td>
							<td class="text-center"></td>
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
			<div class="an-component-body an-helper-block" style="max-height: 300px; overflow-y: scroll">
				<?php echo nl2br($step->goal) ?>
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_attendees'))?></h6>
			</div>
			<div class="an-component-body an-helper-block">
				<?php foreach ($step->members as $member) : ?>
				<div class="attendee">
					<div class="info"><?php echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar) ?></div>
					<div class="rating">
						<input type="radio" id="star5" name="attendee_rate[<?php echo $member->user_id ?>]" <?php echo set_radio('attendee_rate[' . $member->user_id . ']', 5) ?> value="5" /><label class = "full" for="star5" title="5 stars"></label>
						<!--input type="radio" id="star4half" name="attendee_rate[<?php echo $member->user_id ?>]" value="4.5" /><label class="half" for="star4half" title="4.5 stars"></label-->
						<input type="radio" id="star4" name="attendee_rate[<?php echo $member->user_id ?>]" <?php echo set_radio('attendee_rate[' . $member->user_id . ']', 4) ?> value="4" /><label class = "full" for="star4" title="4 stars"></label>
						<!--input type="radio" id="star3half" name="attendee_rate[<?php echo $member->user_id ?>]" value="3.5" /><label class="half" for="star3half" title="3.5 stars"></label-->
						<input type="radio" id="star3" name="attendee_rate[<?php echo $member->user_id ?>]" <?php echo set_radio('attendee_rate[' . $member->user_id . ']', 3) ?> value="3" /><label class = "full" for="star3" title="3 stars"></label>
						<!--input type="radio" id="star2half" name="attendee_rate[<?php echo $member->user_id ?>]" value="2.5" /><label class="half" for="star2half" title="2.5 stars"></label-->
						<input type="radio" id="star2" name="attendee_rate[<?php echo $member->user_id ?>]" <?php echo set_radio('attendee_rate[' . $member->user_id . ']', 2) ?> value="2" /><label class = "full" for="star2" title="2 stars"></label>
						<!--input type="radio" id="star1half" name="attendee_rate[<?php echo $member->user_id ?>]" value="1.5" /><label class="half" for="star1half" title="1.5 stars"></label-->
						<input type="radio" id="star1" name="attendee_rate[<?php echo $member->user_id ?>]" <?php echo set_radio('attendee_rate[' . $member->user_id . ']', 1) ?> value="1" /><label class = "full" for="star1" title="1 star"></label>
						<!--input type="radio" id="starhalf" name="attendee_rate[<?php echo $member->user_id ?>]" value="0.5" /><label class="half" for="starhalf" title="0.5 stars"></label-->
					</div>
				</div>
				<?php endforeach ?>
			</div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_tasks'))?></h6>
			</div>
			<div class="an-component-body an-helper-block">
				<table class="table table-striped table-task">
					<thead>
						<tr>
							<th><?php e(lang('st_name'))?></th>
							<th class="text-center"><?php e(lang('st_started'))?></th>
							<th class="text-center"><?php e(lang('st_duration'))?></th>
							<th class="text-center"><?php e(lang('st_status'))?></th>
							<th class="text-center"><?php e(lang('st_confirm_status'))?></th>
							<th><?php e(lang('st_rate'))?></th>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($tasks)): foreach ($tasks as $task) : ?>
						<tr>
							<td><?php echo anchor(site_url('task/' . $task->task_key), $task->name, ['target' => '_blank'])?></td>
							<td class="text-center"><?php e(empty($task->started_on) ? '' : $task->started_on) ?></td>
							<td class="text-center">
							<?php
							if (! empty($task->started_on) && ! empty($task->finished_on)) {
								$duration = strtotime($task->finished_on) - strtotime($task->started_on);
								if ($duration >= 0) {
									echo ($duration / 60) . ' ' . lang(($duration == 1 ? 'st_minute' : 'st_minutes'));
								}
							}
							?>
							</td>
							<td class="text-center task-status">
								<?php if (! empty($task->status)) : ?>
								<span class="<?php e($task_status_labels[$task->status] . ' label-' . $task->status)?>"><?php e(lang('st_' . $task->status))?></span>
								<?php endif ?>
							</td>
							<td class="text-center task-status">
								<?php if (! empty($task->confirm_status)) : ?>
								<span class="<?php e($task_confirm_status_labels[$task->confirm_status] . ' label-' . $task->confirm_status)?>"><?php e(lang('st_' . $task->confirm_status))?></span>
								<?php endif ?>
							</td>
							<td>
								<div class="rating">
									<input type="radio" id="star5" name="task_rate[<?php echo $task->task_id ?>]" <?php echo set_radio('task_rate[' . $task->task_id . ']', 5) ?> value="5" /><label class = "full" for="star5" title="5 stars"></label>
									<!--input type="radio" id="star4half" name="task_rate[<?php echo $task->task_id ?>]" value="4.5" /><label class="half" for="star4half" title="4.5 stars"></label-->
									<input type="radio" id="star4" name="task_rate[<?php echo $task->task_id ?>]" <?php echo set_radio('task_rate[' . $task->task_id . ']', 4) ?> value="4" /><label class = "full" for="star4" title="4 stars"></label>
									<!--input type="radio" id="star3half" name="task_rate[<?php echo $task->task_id ?>]" value="3.5" /><label class="half" for="star3half" title="3.5 stars"></label-->
									<input type="radio" id="star3" name="task_rate[<?php echo $task->task_id ?>]" <?php echo set_radio('task_rate[' . $task->task_id . ']', 3) ?> value="3" /><label class = "full" for="star3" title="3 stars"></label>
									<!--input type="radio" id="star2half" name="task_rate[<?php echo $task->task_id ?>]" value="2.5" /><label class="half" for="star2half" title="2.5 stars"></label-->
									<input type="radio" id="star2" name="task_rate[<?php echo $task->task_id ?>]" <?php echo set_radio('task_rate[' . $task->task_id . ']', 2) ?> value="2" /><label class = "full" for="star2" title="2 stars"></label>
									<!--input type="radio" id="star1half" name="task_rate[<?php echo $task->task_id ?>]" value="1.5" /><label class="half" for="star1half" title="1.5 stars"></label-->
									<input type="radio" id="star1" name="task_rate[<?php echo $task->task_id ?>]" <?php echo set_radio('task_rate[' . $task->task_id . ']', 1) ?> value="1" /><label class = "full" for="star1" title="1 star"></label>
									<!--input type="radio" id="starhalf" name="task_rate[<?php echo $task->task_id ?>]" value="0.5" /><label class="half" for="starhalf" title="0.5 stars"></label-->
								</div>
							</td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>

	<div class="col-md-12">
		<label><?php echo lang('st_notes') ?></label>
		<div>
		<?php echo nl2br($step->notes) ?>
		</div>
	</div>

	<div class="col-md-12" style="padding-bottom: 30px;">
		<div class="row">
			<div class="col-md-8">
				<label><?php echo lang('st_point_value') ?></label>
				<span><?php echo $point_used ?></span>
			</div>

			<div class="col-md-4">
				<button type="submit" id="submit_evaluator" class="btn btn-primary pull-right" name="save"><?php echo lang('st_submit') ?></button>
			</div>
		</div>
	</div>
</div>
<?php echo form_close() ?>
<script>
	<?php
	echo $this->load->view('evaluator_js', [
		'step_key' => $step->step_key
	], true);
	?>
</script>