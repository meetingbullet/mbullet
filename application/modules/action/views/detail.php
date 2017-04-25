<?php 

$action_button = [
	'open' => [
		'an-btn-primary',
		'ac_start_action'
	],
	'inprogress' => [
		'an-btn-success',
		'ac_ready'
	],
	'ready' => [
		'an-btn-danger-transparent',
		'ac_resolve'
	],
	'resolved' => [
		'an-btn-danger-transparent',
		'ac_resolved'
	]
]

?>

<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h5 class='breakcumb'>
			<?php echo $action_key ?>
		</h5>
		<h2><?php e($action->name)?></h2>

	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<?php echo form_open() ?>
<div class="btn-block">
	<?php echo anchor('#', '<i class="ion-edit"></i> ' . lang('ac_edit'), ['class' => 'an-btn an-btn-primary']) ?>
	<button name='update' class='an-btn <?php echo $action_button[$action->status][0] ?>' <?php echo $action->status == 'resolved' ? ' disabled' : ''?>>
		<i class="ion-ios-play"></i> <?php echo lang($action_button[$action->status][1])?>
	</button>
</div>
<?php echo form_close() ?>

<div class="row">
		<div class="col-md-8">
			<div class="an-single-component with-shadow">
				<div class="an-component-header">
					<h6><?php e(lang('ac_detail'))?> </h6>
				</div>
				<div class="an-component-body">
					<div class="an-helper-block action-detail">
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_owner'))?></div>
							<div class="col-md-8"><?php e($action->owner_name)?></div>
						</div>
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_success_condition'))?></div>
							<div class="col-md-8"><?php e($action->success_condition)?></div>
						</div>
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_type'))?></div>
							<div class="col-md-8"><?php e($action->action_type)?></div>
						</div>
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_component_steps'))?></div>
							<div class="col-md-8"><?php e($action->success_condition)?></div>
						</div>
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_point_value_defined'))?></div>
							<div class="col-md-8"><?php e($action->point_value_defined)?></div>
						</div>
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_point_used'))?></div>
							<div class="col-md-8"><?php e($action->point_used)?></div>
						</div>
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_avarage_stars'))?></div>
							<div class="col-md-8"><?php e($action->avarage_stars)?></div>
						</div>
					</div> <!-- end .AN-HELPER-BLOCK -->
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div> <!-- end .AN-SINGLE-COMPONENT  -->

			<div class="an-single-component with-shadow">
				<div class="an-component-header">
					<h6><?php e(lang('ac_steps'))?></h6>
					</div>
				<div class="an-component-body">
					<div class="an-helper-block">
						<div class="an-scrollable-x">
							<table class="table table-striped table-step">
								<thead>
									<tr>
										<th><?php e(lang('ac_key'))?></th>
										<th><?php e(lang('ac_name'))?></th>
										<th><?php e(lang('ac_owner'))?></th>
										<th><?php e(lang('ac_status'))?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($steps as $step): ?>
									<tr>
										<td><?php e(sprintf(lang('ac_step_key'), $step->step_key))?></td>
										<td><?php e($step->name)?></td>
										<td><?php e($step->owner_name)?></td>
										<td><?php e($step->status)?></td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<button class="an-btn an-btn-primary" id="add-step"><?php echo '<i class="ion-android-add"></i> ' . lang('ac_add_step')?></button>
					</div> <!-- end .AN-HELPER-BLOCK -->
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div>
		</div>

		<div class="col-md-4">
			<div class="an-single-component with-shadow">
				<div class="an-component-header">
					<h6><?php e(lang('ac_resource'))?></h6>
				</div>
				<div class="an-component-body">
					<div class="an-helper-block">
						<div class="an-input-group">
							<input type="text" id="team-member" class="select-member select-member-no-border an-tags-input" placeholder="<?php e(lang('ac_add_team_member'))?>" value="<?php echo implode(',', $invited_members) ?> ">
						</div>
					</div> <!-- end .AN-HELPER-BLOCK -->
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div> <!-- end .AN-SINGLE-COMPONENT  -->

			<div class="an-single-component with-shadow">
				<div class="an-component-header">
					<h6><?php e(lang('ac_date'))?></h6>
				</div>
				<div class="an-component-body">
					<div class="an-helper-block action-detail">
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_created'))?></div>
							<div class="col-md-8"><?php e($action->created_on)?></div>
						</div>
						<div class="row">
							<div class="col-md-4"><?php e(lang('ac_updated'))?></div>
							<div class="col-md-8"><?php e($action->modified_on)?></div>
						</div>
					</div> <!-- end .AN-HELPER-BLOCK -->
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div> <!-- end .AN-SINGLE-COMPONENT  -->
		</div>
</div>

<!-- Modal -->
<div class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>