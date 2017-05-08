<?php

$cost_of_time_to_badge = [
	'', // Skip cost_of_time_to_badge[0]
	'default',	// XS
	'info',	// S
	'success',		// M
	'primary',	// L
	'warning',	// XL
];

$task_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
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
?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h2><?php e($step->name)?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->

<div class="btn-block">
	<?php echo anchor(site_url('action/' . $action_key), '<i class="ion-android-arrow-back"></i> ' . lang('st_back'), ['class' => 'an-btn an-btn-primary' ]) ?>
	<a href='#' id="edit-step" class='an-btn an-btn-primary'><i class="ion-edit"></i> <?php echo lang('st_edit')?></a>
	<!--<a class="an-btn an-btn-primary" 
		id="change-step-status" 
		data-next-status="<?php echo $buttons[$step->status]['next_status'] ?>" 
		data-update-status-url="<?php echo base_url('/step/update_status/' . $step_key . '?status=' . urlencode($buttons[$step->status]['next_status'])) ?>"
	>
		<i class="<?php echo $buttons[$step->status]['icon'] ?>"></i> <?php echo $buttons[$step->status]['label'] ?>
	</a>-->

	<a href='#' id="open-step-monitor" class='an-btn an-btn-primary<?php echo $step->status == 'open' ? ' step-open' : ''?><?php echo $step->status == 'open' || $step->status == 'ready' || $step->status == 'inprogress' ? '' : ' hidden'?>'>
		<?php 
			if ($step->status == 'open') {
				echo '<i class="ion-ios-play"></i> '. lang('st_set_up');
			} else {
				echo '<i class="ion-ios-eye"></i> ' . lang($step->owner_id == $current_user->user_id ? 'st_open_step_monitor' :  'st_join_step_monitor');
			}
		?>
	</a>
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
						<div class="col-xs-8"><?php e($step->owner_name)?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_goal'))?></div>
						<div class="col-xs-8"><?php e($step->goal)?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_status'))?></div>
						<div class="col-xs-8" id="status"><?php e(str_replace('-', ' ', $step->status))?></div>
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
								</tr>
							</thead>
							<tbody>
								<?php if($tasks): foreach ($tasks as $task) : ?>
								<tr>
									<td class='basis-10'><?php e($task->task_key) //anchor(site_url('task/' . $task->task_key), $task->task_key)?></td>
									<td class='basis-15'><?php e($task->name) //anchor(site_url('task/' . $task->task_key), $task->name)?></td>
									<td class='basis-30'><?php echo word_limiter($task->description, 35)?></td>
									<td class='basis-20'>
										<ul class="list-inline list-member">
											<?php if ($task->members) {
												foreach ($task->members as $member) {
													echo '<li><div class="avatar" style="background-image: url(\'' . avatar_url($member->avatar, $member->email) . '\')"></div></li>';
												}
											} ?>
										</ul>
									</td>
									<td class='basis-10'>
										<span class="<?php e($task_status_labels[$task->status] . ' label-' . $task->status)?>"><?php e(lang('st_' . $task->status))?></span>
									</td>
								</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>

					<button class="an-btn an-btn-primary" data-toggle="modal" data-add-task-url="<?php echo site_url('task/create/' . $step_key) ?>" data-target="#bigModal" data-backdrop="static" id="add-task"><?php echo '<i class="ion-android-add"></i> ' . lang('st_add_task')?></button>
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
							<?php if ($invited_members): foreach ($invited_members as $user): 
								$user->avatar = avatar_url($user->avatar, $user->email);
							?>
							<li>
								<div class="avatar" style="background-image: url('<?php echo $user->avatar ?>')"></div>
								<?php e($user->name)?>

								<span class="badge badge-<?php e($cost_of_time_to_badge[$user->cost_of_time])?> badge-bordered pull-right"><?php e($user->cost_of_time_name)?></span>
							</li>
							<?php endforeach; endif;?>
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
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_created'))?></div>
						<div class="col-xs-8"><?php e($step->created_on)?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_updated'))?></div>
						<div class="col-xs-8"><?php e($step->modified_on)?></div>
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