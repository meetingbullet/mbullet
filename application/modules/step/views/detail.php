<?php
$buttons = [
	'open' => [
		'icon' => 'ion-ios-play',
		'label' => lang('st_start_step'),
		'next_status' => 'in-progress',
	],
	'in-progress' => [
		'icon' => 'ion-android-done',
		'label' => lang('st_ready'),
		'next_status' => 'ready-for-review',
	],
	'ready-for-review' => [
		'icon' => 'ion-android-done-all',
		'label' => lang('st_resolve'),
		'next_status' => 'resolved',
	],
	'resolved' => [
		'icon' => 'ion-ios-book',
		'label' => lang('st_reopen'),
		'next_status' => 'open',
	]
];
?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h5 class='breadcrumb'>
			<?php echo anchor(site_url('project/' . $project_key), $project_key) ?> /
			<?php e($step_key) ?>
		</h5>
		<h2><?php e($step->name)?></h2>

	</div>
</div> <!-- end AN-BODY-TOPBAR -->

<div class="btn-block">
	<?php echo anchor('#', '<i class="ion-edit"></i> ' . lang('st_edit'), ['class' => 'an-btn an-btn-primary']) ?>
	<a class="an-btn an-btn-primary" id="change-step-status" data-next-status="<?php echo $buttons[$step->status]['next_status'] ?>" data-update-status-url="<?php echo base_url('/step/update_status/' . $step_key . '?status=' . urlencode($buttons[$step->status]['next_status'])) ?>"><i class="<?php echo $buttons[$step->status]['icon'] ?>"></i> <?php echo $buttons[$step->status]['label'] ?></a>
</div>

<div class="row">
	<div class="col-md-8">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_detail'))?> </h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block step-detail">
					<div class="row">
						<div class="col-md-4"><?php e(lang('st_owner'))?></div>
						<div class="col-md-8"><?php e($step->owner_name)?></div>
					</div>
					<div class="row">
						<div class="col-md-4"><?php e(lang('st_goal'))?></div>
						<div class="col-md-8"><?php e($step->goal)?></div>
					</div>
					<div class="row">
						<div class="col-md-4"><?php e(lang('st_status'))?></div>
						<div class="col-md-8" id="status"><?php e(str_replace('-', ' ', $step->status))?></div>
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
									<th><?php e(lang('st_owner'))?></th>
									<th><?php e(lang('st_status'))?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($tasks as $task) : ?>
								<tr>
									<td><?php e($task->task_key)?></td>
									<td><?php e($task->name)?></td>
									<td><?php e($task->owner_name)?></td>
									<td><?php e($task->status)?></td>
								</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>

					<button class="an-btn an-btn-primary" id="add-task"><?php echo '<i class="ion-android-add"></i> ' . lang('st_add_task')?></button>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>

	<div class="col-md-4">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_resource'))?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block">
					<div class="an-input-group">
						<div class="an-input-group-addon"><i class="ion-ios-search"></i></div>
						<input type="text" id="team-member" class="select-member an-tags-input" placeholder="<?php e(lang('st_add_team_member'))?>" value="<?php echo implode(',', $invited_members) ?> ">
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
						<div class="col-md-4"><?php e(lang('st_created'))?></div>
						<div class="col-md-8"><?php e($step->created_on)?></div>
					</div>
					<div class="row">
						<div class="col-md-4"><?php e(lang('st_updated'))?></div>
						<div class="col-md-8"><?php e($step->modified_on)?></div>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->
	</div>
</div>