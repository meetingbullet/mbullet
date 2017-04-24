<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h5 class='breadcrumb'>
			<?php echo anchor(site_url('project/' . $project_key), sprintf(lang('st_project_key'), $project_key)) ?> /
			<?php echo sprintf(lang('st_action_key'), $action_key) ?>
		</h5>
		<h2><?php e($step->name)?></h2>

	</div>
</div> <!-- end AN-BODY-TOPBAR -->

<div class="btn-block">
	<?php echo anchor('#', '<i class="ion-edit"></i> ' . lang('st_edit'), ['class' => 'an-btn an-btn-primary']) ?>
	<?php echo anchor('#', '<i class="ion-ios-play"></i> ' . lang('st_start_action'), ['class' => 'an-btn an-btn-primary']) ?>
</div>

<div class="row">
	<div class="col-md-8">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_detail'))?> </h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block action-detail">
					<div class="row">
						<div class="col-md-4"><?php e(lang('st_owner'))?></div>
						<div class="col-md-8"><?php e($step->owner_name)?></div>
					</div>
					<div class="row">
						<div class="col-md-4"><?php e(lang('st_goal'))?></div>
						<div class="col-md-8"><?php e($step->goal)?></div>
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
								<?php //foreach ($tasks as $task): ?>
								<tr>
									<td><?php //e(sprintf(lang('st_task_key'), $task->task_key))?></td>
									<td><?php //e($task->name)?></td>
									<td><?php //e($task->owner_name)?></td>
									<td><?php //e($task->status)?></td>
								</tr>
								<?php //endforeach; ?>
							</tbody>
						</table>
					</div>

					<button class="an-btn an-btn-primary" id="add-step"><?php echo '<i class="ion-android-add"></i> ' . lang('st_add_step')?></button>
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
						<input type="text" class="an-form-control" placeholder="<?php e(lang('st_team_member'))?>">
					</div>

					<div class="user-group">
						<div class='user-single'>
							<i class="ion-close-round remove"></i>
							<span class='name'>User Demo</span>
						</div>
						<div class='user-single'>
							<i class="ion-close-round remove"></i>
							<span class='name'>User Demo</span>
						</div>
						<div class='user-single'>
							<i class="ion-close-round remove"></i>
							<span class='name'>User Demo</span>
						</div>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_date'))?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block action-detail">
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