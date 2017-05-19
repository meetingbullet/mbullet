<?php

$scheduled_start_time = null;
$is_owner = $step->owner_id == $current_user->user_id;

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
		<h2 id="step-name"><?php e($step->name)?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->

<div class="btn-block">
	<?php echo anchor(site_url('action/' . $action_key), '<i class="ion-android-arrow-back"></i> ' . lang('st_back'), ['class' => 'an-btn an-btn-primary' ]) ?>
	<a href='#' id="edit-step" class='an-btn an-btn-primary'><i class="ion-edit"></i> <?php echo lang('st_edit')?></a>
	<?php if (in_array($current_user->user_id, $members) || $is_owner) : ?>
		<?php if ($step->status == 'open'): ?>
			<?php if ($is_owner): ?>
				<a href='#' id="open-step-monitor" class='an-btn an-btn-primary step-open'>
					<i class="ion-ios-play"></i> <?php e(lang('st_set_up')); ?>
				</a>
			<?php endif; ?>
		<?php elseif ($step->status == 'ready' || $step->status == 'inprogress'): ?>
			<?php if ($is_owner): ?>
			<a href='#' id="open-step-monitor" class='an-btn an-btn-primary'>
				<i class="ion-ios-play"></i> <?php e(lang('st_monitor')); ?>
			</a>
			<?php elseif ($step->status == 'inprogress') : ?>
			<a href='#' id="open-step-monitor" class='an-btn an-btn-primary'>
				<i class="ion-ios-play"></i> <?php e(lang('st_join')); ?>
			</a>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($step->manage_state == 'decide' && $is_owner): ?>
	<a href='#' id="open-step-decider" class='an-btn an-btn-primary'><i class="ion-play"></i> <?php echo lang('st_decider')?></a>
	<?php endif; ?>
	<?php if ($step->manage_state == 'evaluate' && $evaluated === false): ?>
	<a href='#' id="open-step-evaluator" data-is-owner="<?php echo $is_owner == true ? '1' : '0' ?>" class='an-btn an-btn-primary'><i class="ion-play"></i> <?php echo lang('st_evaluator')?></a>
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
						<div class="col-xs-8">
							<div class="step-goal-container">
								<div class="step-goal">
									<?php echo $step->goal?></div>
								</div>
							</div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_status'))?></div>
						<div class="col-xs-8">
							<span class="<?php e($label[$step->status])?>" id="step-status" data-status="<?php e($step->status)?>" data-is-owner="<?php e($is_owner ? 1 : 0)?>"><?php e(lang('st_' . $step->status))?></span>
						</div>

					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_point_used')) ?></div>
						<div class="col-xs-8"><?php e($point_used) ?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_scheduled_duration')) ?></div>
						<div class="col-xs-8"><?php e($step->in . ' ' . lang('st_' . $step->in_type)) ?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(ucfirst(lang('st_actual_duration')))?></div>
						<div class="col-xs-8"><?php echo round($step->actual_elapsed_time, 2) . ' ' . lang('st_minutes') ?></div>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_tasks'))?></h6>
				</div>
			<div id="task-list" class="an-component-body">
				<div class="an-helper-block">
					<div class="an-scrollable-x">
						<table class="table table-striped table-detail-task">
							<thead>
								<tr>
									<th><?php e(lang('st_key'))?></th>
									<th><?php e(lang('st_name'))?></th>
									<th><?php e(lang('st_description'))?></th>
									<th><?php e(lang('st_assignee'))?></th>
									<th><?php e(lang('st_status'))?></th>
									<?php if ($step->status == 'finished' || $step->status == 'resolved') : ?>
									<th><?php e(lang('st_confirmation_status'))?></th>
									<?php endif ?>
								</tr>
							</thead>
							<tbody>
								<?php if($tasks): foreach ($tasks as $task) : ?>
								<tr data-task-id="<?php e($task->task_id) ?>" data-confirm-status="<?php e($task->confirm_status) ?>">
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
										<span class="label label-bordered label-<?php e($task->status)?>"><?php e(lang('st_' . $task->status))?></span>
									</td>
									<?php if ($step->status == 'finished' || $step->status == 'resolved') : ?>
									<td class='basis-10 task-status'>
										<?php if ( isset($task_status_labels[$task->confirm_status]) ): ?>
										<span class="<?php e($task_status_labels[$task->confirm_status] . ' label-' . $task->confirm_status)?>"><?php e(lang('st_' . $task->confirm_status))?></span>
										<?php endif; ?>
									</td>
									<?php endif ?>
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

		<?php if (! empty($step->notes)) : ?>
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_notes'))?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block">
					<div class="an-input-group step-notes">
						<?php echo nl2br($step->notes) ?>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->
		<?php endif; ?>

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
						<div class="col-xs-7"><?php e(display_time($step->actual_start_time)); ?></div>
					</div>

					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_actual_end_time'))?></div>
						<?php if ($step->status == 'inprogress') : ?>
						<div class="col-xs-7"><?php e(lang('st_actual_end_time_still_inprogress')); ?></div>
						<?php else: ?>
						<div class="col-xs-7"><?php e(display_time($step->actual_start_time)); ?></div>
						<?php endif; ?>
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
<script type="text" id="task-row">
	<tr data-task-id="{{:task_id}}" data-confirm-status="">
		<td class="basis-10">{{:task_id}}</td>
		<td class="basis-15">{{:name}}</td>
		<td class="basis-20">{{:description}}</td>
		<td class="basis-20">
			{{:assignees}}
		</td>
		<td class="basis-10 task-status">
			<span class="label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</td>
	</tr>
</script>