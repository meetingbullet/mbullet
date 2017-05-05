<?php
$scheduled_start_time = null;
$scheduled_end_time = null;

if ($step->scheduled_start_time) {
	$scheduled_start_time = new DateTime($step->scheduled_start_time);
	$scheduled_start_time = date_format($scheduled_start_time, 'M d, H:i');
}

if ($step->scheduled_end_time) {
	$scheduled_end_time = new DateTime($step->scheduled_end_time);
	$scheduled_end_time = date_format($scheduled_end_time, 'M d, H:i');
}

$scheduled_time = $scheduled_start_time && $scheduled_end_time ? $scheduled_start_time . ' - ' . $scheduled_end_time : '';

$task_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
];
?>
<div data-step-id="<?php e($step->step_id)?>" class="step-monitor" data-is-owner="<?php echo $step->owner_id == $current_user->user_id ? 1 : 0 ?>">
	<?php if (IS_AJAX): ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_step_monitor'))?></h4>
	</div> <!-- end MODAL-HEADER -->
	<?php endif; ?>

	<div class="an-body-topbar">
		<div class="an-page-title">
			<div class="an-bootstrap-custom-tab">
				<h2><?php e($step->name . ' - ' . lang('st_dashboard'))?></h2>
			</div>
		</div>
		<div class="pull-right">
			<div class="an-bootstrap-custom-tab">
				<div class="step-time-schedule">
					<?php echo form_open(site_url('step/update_step_schedule'), ['class' => 'form-inline form-step-schedule']) ?>
						<input type="hidden" name="step_id" value="<?php e($step->step_id) ?>">
						<input type="hidden" name="scheduled_start_time" id="scheduled_start_time" value="<?php e($step->scheduled_start_time)?>" />
						<input type="hidden" name="scheduled_end_time" id="scheduled_end_time" value="<?php e($step->scheduled_end_time)?>">

						<h3 id="scheduled-timer" class="step-action hidden" data-now="<?php e($now)?>" data-actual-start-time="<?php echo $step->status == 'inprogress' ? $step->actual_start_time : ''?>"></h3>
						<div class="an input-group <?php echo $step->status == 'open' ? ' input-group-btn-right' : '' ?>">
							<div class="input-group-addon"><i class="ion-android-calendar"></i></div>
							<input type="text" 
									name="scheduled_time" 
									class="form-control" 
									value="<?php echo $scheduled_time ?>" 
									placeholder="<?php e(lang('st_time_schedule'))?>" <?php echo $step->status == 'open' ? 'readOnly' : 'disabled' ?>/>
							<span class="input-group-btn">
								<button type="submit" 
										name='save-time' 
										class="an-btn an-btn-danger-transparent btn-update-step-schedule<?php echo $step->status == 'open' ? '' : ' hidden' ?>">
									<i class="glyphicon glyphicon-floppy-disk"></i> <?php e(lang('st_save'))?>
								</button>
							</span>
						</div>

						<div class="step-action">
							<button type="submit" 
									name='start-step' 
									class="an-btn an-btn-danger btn-start-step<?php echo $step->status == 'open' ? '' : ' hidden' ?>"
								<?php echo is_null($step->scheduled_start_time) || is_null($step->scheduled_end_time) ? ' disabled' : '' ?> >
								<i class="ion-ios-play"></i> <?php e(lang('st_start'))?>
							</button>
							<button class="an-btn an-btn-success btn-finish<?php echo $step->status == 'inprogress' ? '' : ' hidden' ?>" disabled>
								<i class="ion-ios-checkmark-outline"></i> <?php e(lang('st_finish'))?>
							</button>
						</div>
					<?php echo form_close() ?>
				</div>
			</div>
		</div>
	</div> <!-- end AN-BODY-TOPBAR -->


	<div class="an-single-component with-shadow">
		<div class="an-component-header">
			<h6><?php e(lang('st_tasks'))?></h6>
			</div>
		<div class="an-component-body an-helper-block">
			<table class="table table-striped table-task">
				<thead>
					<tr>
						<th><?php e(lang('st_name'))?></th>
						<th class='text-center'><?php e(lang('st_time_assigned'))?></th>
						<th class='text-center'><?php e(lang('st_skip_votes'))?></th>
						<th><?php e(lang('st_status'))?></th>
						<th><?php e(lang('st_action'))?></th>
					</tr>
				</thead>
				<tbody>
					<?php if($tasks): foreach ($tasks as $task) : ?>
					<tr id='task-<?php e($task->task_id)?>' data-task-id='<?php e($task->task_id)?>' data-task-status='<?php e($task->status)?>'>
						<td><?php echo anchor(site_url('task/' . $task->task_key), $task->name, ['target' => '_blank'])?></td>
						<td class='text-center'>
							<span class="time-assigned">
								<?php e($task->time_assigned)?>
							</span>

							<input type="number" name="time_assigned" class='an-form-control form-td<?php echo ($task->time_assigned == NULL ? '' : ' hidden' ) ?>' step="0.01" value="<?php e($task->time_assigned)?>"/>
						</td>
						<td class='text-center skip-votes'><?php e($task->skip_votes)?></td>
						<td class='task-status basis-30' <?php echo $task->status == 'inprogress' ? "data-now='{$now}' data-started-on='{$task->started_on}' data-time-assigned='{$task->time_assigned}'" : '' ?>>
							<span class="<?php e($task_status_labels[$task->status] . ' label-' . $task->status)?>"><?php e(lang('st_' . $task->status))?></span>
						</td>

						<?php if ($step->owner_id == $current_user->user_id): ?>
						<td>
							<button class="an-btn an-btn-primary btn-start-task<?php e($step->status == 'inprogress' && $task->status == 'open' ? '' : ' hidden')?>"<?php e($task->time_assigned ? '' : ' disabled')?>>
								<?php e(lang('st_start'))?>
							</button>
							<button class="an-btn an-btn-primary btn-skip<?php e($step->status == 'inprogress' && $task->status == 'open' ? '' : ' hidden')?>"><?php e(lang('st_skip'))?></button>
							<button class="an-btn an-btn-primary btn-jump<?php e($task->status == 'inprogress' ? '' : ' hidden')?>"><?php e(lang('st_jump'))?></button>
						</td>
						<?php else: ?>
						<td>
							<?php if ($task->voted_skip == 0):?>
							<button class="an-btn an-btn-primary btn-vote-skip<?php e($task->status == 'inprogress' ? '' : ' hidden')?>"><?php e(lang('st_vote_skip'))?></button>
							<?php else: ?>
							<button class="an-btn an-btn-primary-transparent<?php e($task->status == 'inprogress' ? '' : ' hidden')?>" disabled><?php e(lang('st_voted_skip'))?></button>
							<?php endif; ?>
						</td>
						<?php endif; ?>
					</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div> <!-- end .AN-COMPONENT-BODY -->
	</div>
</div>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('monitor_js', [
			
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