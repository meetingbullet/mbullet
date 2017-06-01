<?php 

$cost_of_time_to_badge = [
	'', // Skip cost_of_time_to_badge[0]
	'default',	// XS
	'info',	// S
	'success',		// M
	'primary',	// L
	'warning',	// XL
];

$meeting_label = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'ready' => 'label label-success label-bordered',
	'finished' => 'label label-info label-bordered',
	'resolved' => 'label label-success label-bordered'
];

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
];

$project_key = explode('-', $action_key);
$project_key = $project_key['0'];
?>

<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h2 id="action-name"><?php e($action->name)?></h2>

	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<?php echo form_open() ?>
<div class="btn-block">
	<?php echo anchor(site_url('project/' . $project_key), '<i class="ion-android-arrow-back"></i> ' . lang('ac_back'), ['class' => 'an-btn an-btn-primary' ]) ?>
	<a data-toggle="modal" data-target="#bigModal" id="update-action" data-update-action-url="<?php echo site_url('action/create/' . $project_key . '/' . $action_key) ?>" class='an-btn an-btn-primary'><i class="ion-edit"></i> <?php echo lang('ac_edit')?></a>

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
							<div class="col-xs-4"><?php e(lang('ac_owner'))?></div>
							<div class="col-xs-8 owner"><?php echo display_user($action->email, $action->first_name, $action->last_name, $action->avatar); ?></div>
						</div>
						<div class="row">
							<div class="col-xs-4"><?php e(lang('ac_success_condition'))?></div>
							<div class="col-xs-8 success"><?php e(lang('ac_' . $action->success_condition))?></div>
						</div>
						<div class="row">
							<div class="col-xs-4"><?php e(lang('ac_type'))?></div>
							<div class="col-xs-8 type"><?php e(lang('ac_' . $action->action_type))?></div>
						</div>
						<div class="row">
							<div class="col-xs-4"><?php e(lang('ac_point_value'))?></div>
							<div class="col-xs-8 point-value"><?php e($action->point_value)?></div>
						</div>
						<div class="row">
							<div class="col-xs-4"><?php e(lang('ac_point_used'))?></div>
							<div class="col-xs-8"><?php e($action->point_used)?></div>
						</div>
						<div class="row">
							<div class="col-xs-4"><?php e(lang('ac_avarage_stars'))?></div>
							<div class="col-xs-8"><?php e(lang('ac_not_rated'))?></div>
						</div>
					</div> <!-- end .AN-HELPER-BLOCK -->
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div> <!-- end .AN-SINGLE-COMPONENT  -->

			<div class="an-single-component with-shadow">
				<div class="an-component-header">
					<h6><?php e(lang('ac_meetings'))?></h6>
					</div>
				<div id="meeting-list" class="an-component-body">
					<div class="an-helper-block">
						<div class="an-scrollable-x">
							<table class="table table-striped table-meeting">
								<thead>
									<tr>
										<th><?php e(lang('ac_key'))?></th>
										<th><?php e(lang('ac_name'))?></th>
										<th><?php e(lang('ac_point_used'))?></th>
										<th><?php e(lang('ac_owner'))?></th>
										<th><?php e(lang('ac_status'))?></th>
									</tr>
								</thead>
								<tbody>
									<?php if (is_array($meetings)) : foreach ($meetings as $meeting): ?>
									<tr data-meeting-id="<?php e($meeting->meeting_id)?> " >
										<td><?php echo anchor(site_url('meeting/' . $meeting->meeting_key), $meeting->meeting_key); ?></td>
										<td><?php echo anchor(site_url('meeting/' . $meeting->meeting_key), $meeting->name); ?></td>
										<td><?php e($meeting->point_used); ?></td>
										<td><?php echo display_user($meeting->email, $meeting->first_name, $meeting->last_name, $meeting->avatar); ?></td>
										<td><span class="label label-bordered label-<?php echo $meeting->status ?>"><?php e(lang('st_' . $meeting->status))?></span></td>
									</tr>
									<?php endforeach; endif;?>
								</tbody>
							</table>
						</div>

						<button class="an-btn an-btn-primary" id="add-meeting"><?php echo '<i class="ion-android-add"></i> ' . lang('ac_add_meeting')?></button>
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
						<ul id="action-resource" class="list-unstyled list-member">
							<?php if (is_array($invited_members)) { foreach ($invited_members as $user) { ?>
							<li>
								<?php echo display_user($user->email, $user->first_name, $user->last_name, $user->avatar); ?>

								<span class="badge badge-<?php e($user->cost_of_time)?> badge-bordered pull-right"><?php e($user->cost_of_time_name)?></span>
							</li>
							<?php } } ?>
						</ul>
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
							<div class="col-xs-5"><?php e(lang('ac_created')); ?></div>
							<div class="col-xs-7"><?php e(display_time($action->created_on)); ?></div>
						</div>
						<div class="row">
							<div class="col-xs-5"><?php e(lang('ac_updated')); ?></div>
							<div class="col-xs-7"><?php e(display_time($action->modified_on)); ?></div>
						</div>
					</div> <!-- end .AN-HELPER-BLOCK -->
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div> <!-- end .AN-SINGLE-COMPONENT  -->
		</div>
</div>

<!-- Modal -->
<div class="modal fade" id="bigModal" tabindex="-1" role="dialog" aria-labelledby="bigModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<script type="text" id="meeting-row">
	<tr data-meeting-id="{{:meeting_id}}" >
		<td><a href="<?php echo site_url('meeting/') ?>{{:meeting_key}}">{{:meeting_key}}</a></td>
		<td><a href="<?php echo site_url('meeting/') ?>{{:meeting_key}}">{{:name}}</a></td>
		<td>0</td>
		<td>{{:display_user}}</td>
		<td><span class="label label-bordered label-{{:status}}">{{:lang_status}}</span></td>
	</tr>
</script>