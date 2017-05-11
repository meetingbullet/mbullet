<?php

$scheduled_start_time = null;

if ($step->scheduled_start_time) {
	$scheduled_start_time = strtotime($step->scheduled_start_time);
	$scheduled_end_time = strtotime('+' . $step->in . ' ' . $step->in_type, $scheduled_start_time);

	$scheduled_start_time = gmdate('Y-m-d H:i:s', $scheduled_start_time);
	$scheduled_end_time = gmdate('Y-m-d H:i:s', $scheduled_end_time);
}

$cost_of_time_to_badge = [
	'', // Skip cost_of_time_to_badge[0]
	'default',	// XS
	'info',	// S
	'success',		// M
	'primary',	// L
	'warning',	// XL
];

$label = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'ready' => 'label label-success label-bordered',
	'finished' => 'label label-info label-bordered',
	'resolved' => 'label label-success label-bordered'
];

$task_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
	'closed' => 'label label-default label-bordered',
	'closed_parking_lot' => 'label label-primary label-bordered',
	'open_parking_lot' => 'label label-primary label-bordered',
];

$buttons = [
	'open' => [
		'icon' => 'ion-ios-play',
		'label' => lang('st_start_step'),
		'next_status' => 'inprogress',
	],
	'inprogress' => [
		'icon' => 'ion-android-done',
		'label' => lang('st_ready'),
		'next_status' => 'ready',
	],
	'ready' => [
		'icon' => 'ion-android-done-all',
		'label' => lang('st_resolve_step'),
		'next_status' => 'resolved',
	],
	'resolved' => [
		'icon' => 'ion-ios-book',
		'label' => lang('st_reopen'),
		'next_status' => 'open',
	]
];

$action_key = explode('-', $step_key);
$action_key = $action_key['0'] . '-' . $action_key[1];
$members = array_column($invited_members, 'user_id');
?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h2><?php e($step->name)?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->

<div class="btn-block">
	<?php echo anchor(site_url('action/' . $action_key), '<i class="ion-android-arrow-back"></i> ' . lang('st_back'), ['class' => 'an-btn an-btn-primary' ]) ?>
	<a href='#' id="edit-step" class='an-btn an-btn-primary'><i class="ion-edit"></i> <?php echo lang('st_edit')?></a>
	<?php if (in_array($current_user->user_id, $members) || $step->owner_id == $current_user->user_id) : ?>
	<a href='#' id="open-step-monitor" class='an-btn an-btn-primary<?php echo $step->status == 'open' ? ' step-open' : ''?><?php echo $step->status == 'open' || $step->status == 'ready' || $step->status == 'inprogress' ? '' : ' hidden'?>'>
		<?php 
			if ($step->status == 'open') {
				echo '<i class="ion-ios-play"></i> '. lang('st_set_up');
			} else {
				echo '<i class="ion-ios-play"></i> ' . lang($step->owner_id == $current_user->user_id ? 'st_monitor' :  'st_join');
			}
		?>
	</a>
	<?php endif; ?>
</div>

<div class="row">
	<div class="col-md-9">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_detail'))?> </h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block step-detail">
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_owner'))?></div>
						<div class="col-xs-8"><?php echo display_user($step->email, $step->first_name, $step->last_name, $step->avatar); ?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_goal'))?></div>
						<div class="col-xs-8 step-goal"><?php echo word_limiter($step->goal, 100)?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_status'))?></div>
						<div class="col-xs-8" id="status">
							<span class="<?php e($label[$step->status])?>"><?php e(lang('st_' . $step->status))?></span>
						</div>

					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_point_used'))?></div>
						<div class="col-xs-8" id="status"><?php e($point_used) ?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(ucfirst(lang('st_in')))?></div>
						<div class="col-xs-8" id="status"><?php e($step->in . ' ' . lang('st_' . $step->in_type)) ?></div>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_tasks'))?></h6>
				</div>
			<div class="an-component-body">
				<div class="an-helper-block">
					<div class="an-scrollable-x">
						<table class="table table-striped">
							<thead>
								<tr>
									<th><?php e(lang('st_key'))?></th>
									<th><?php e(lang('st_name'))?></th>
									<th><?php e(lang('st_description'))?></th>
									<th><?php e(lang('st_assignee'))?></th>
									<th><?php e(lang('st_status'))?></th>
									<?php if ($step->status == 'finished') : ?>
									<th><?php e(lang('st_confirmation_status'))?></th>
									<?php endif ?>
								</tr>
							</thead>
							<tbody>
								<?php if($tasks): foreach ($tasks as $task) : ?>
								<tr>
									<td class='basis-10'><?php e($task->task_key) //anchor(site_url('task/' . $task->task_key), $task->task_key)?></td>
									<td class='basis-15'><?php e($task->name) //anchor(site_url('task/' . $task->task_key), $task->name)?></td>
									<td class='basis-20'><?php echo word_limiter($task->description, 20)?></td>
									<td class='basis-20'>
										<?php if ($task->members) {
											foreach ($task->members as $member) {
												echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar, true) . ' ';
											}
										} ?>
									</td>
									<td class='basis-10 task-status'>
										<span class="<?php e($task_status_labels[$task->status] . ' label-' . $task->status)?>"><?php e(lang('st_' . $task->status))?></span>
									</td>
									<td class='basis-10 task-status'>
										<span class="<?php e($task_status_labels[$task->confirm_status] . ' label-' . $task->confirm_status)?>"><?php e(lang('st_' . $task->confirm_status))?></span>
									</td>
								</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>

					<?php if ($step->status == 'open'): ?>
					<button class="an-btn an-btn-primary" data-toggle="modal" data-add-task-url="<?php echo site_url('task/create/' . $step_key) ?>" data-target="#bigModal" data-backdrop="static" id="add-task"><?php echo '<i class="ion-android-add"></i> ' . lang('st_add_task')?></button>
					<?php endif; ?>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>

	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_resource'))?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block">
					<div class="an-input-group">
						<ul class="list-unstyled list-member">
							<?php if ($invited_members) { foreach ($invited_members as $user) { ?>
							<li>
								<?php echo display_user($user['email'], $user['first_name'], $user['last_name'], $user['avatar']); ?>

								<span class="badge badge-<?php e($cost_of_time_to_badge[$user['cost_of_time']])?> badge-bordered pull-right"><?php e($user['cost_of_time_name'])?></span>
							</li>
							<?php } } ?>
						</ul>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_date'))?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block step-detail">
					<?php if ($scheduled_start_time): ?>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_scheduled_start_time'))?></div>
						<div class="col-xs-7"><?php e(display_time($scheduled_start_time)); ?></div>
					</div>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_scheduled_end_time'))?></div>
						<div class="col-xs-7"><?php e(display_time($scheduled_end_time)); ?></div>
					</div>
					<hr/>
					<?php endif; ?>
					<?php if ($step->actual_start_time): ?>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_actual_start_time'))?></div>
						<div class="col-xs-7"><?php e(display_time($step->$actual_start_time)); ?></div>
					</div>

					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_actual_end_time'))?></div>
						<div class="col-xs-7"><?php e((! empty($step->$actual_end_time)) ? display_time($step->$actual_end_time) : lang('st_actual_end_time_still_inprogress')); ?></div>
					</div>
					<hr/>
					<?php endif; ?>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_created'))?></div>
						<div class="col-xs-7"><?php e(display_time($step->created_on)); ?></div>
					</div>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_updated'))?></div>
						<div class="col-xs-7"><?php e(display_time($step->modified_on)); ?></div>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->
	</div>
</div>

<!-- Modal -->
<div class="modal modal-monitor fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
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

<div id="bigModal" class="modal modal-edit fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
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