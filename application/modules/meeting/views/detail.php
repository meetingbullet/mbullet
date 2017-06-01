<?php

$scheduled_start_time = null;
$is_owner = $meeting->owner_id == $current_user->user_id;

if ($meeting->scheduled_start_time) {
	$scheduled_start_time = strtotime($meeting->scheduled_start_time);
	$scheduled_end_time = strtotime('+' . $meeting->in . ' ' . $meeting->in_type, $scheduled_start_time);

	$scheduled_start_time = gmdate('Y-m-d H:i:s', $scheduled_start_time);
	$scheduled_end_time = gmdate('Y-m-d H:i:s', $scheduled_end_time);
}

$label = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'ready' => 'label label-success label-bordered',
	'finished' => 'label label-info label-bordered',
	'resolved' => 'label label-success label-bordered'
];

$buttons = [
	'open' => [
		'icon' => 'ion-ios-play',
		'label' => lang('st_start_meeting'),
		'next_status' => 'inprogress',
	],
	'inprogress' => [
		'icon' => 'ion-android-done',
		'label' => lang('st_ready'),
		'next_status' => 'ready',
	],
	'ready' => [
		'icon' => 'ion-android-done-all',
		'label' => lang('st_resolve_meeting'),
		'next_status' => 'resolved',
	],
	'resolved' => [
		'icon' => 'ion-ios-book',
		'label' => lang('st_reopen'),
		'next_status' => 'open',
	]
];

$action_key = explode('-', $meeting_key);
$action_key = $action_key['0'] . '-' . $action_key[1];
$members = array_column($invited_members, 'user_id');
?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h2 id="meeting-name"><?php e($meeting->name)?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->

<div class="btn-block">
	<?php echo anchor(site_url('action/' . $action_key), '<i class="ion-android-arrow-back"></i> ' . lang('st_back'), ['class' => 'an-btn an-btn-primary' ]) ?>
	<a href='#' id="edit-meeting" class='an-btn an-btn-primary'><i class="ion-edit"></i> <?php echo lang('st_edit')?></a>
	<?php if ($meeting->status == 'open'): ?>
		<a href='#' class='mb-open-modal open-meeting-monitor an-btn an-btn-primary meeting-open<?php echo ($is_owner ? '' : ' hidden')?>'
			data-modal-id="meeting-monitor-modal"
			data-url="<?php e(site_url('meeting/monitor/' . $meeting_key)) ?>" 
			data-modal-dialog-class="modal-80"
		>
			<i class="ion-ios-play"></i> <?php e(lang('st_set_up')); ?>
		</a>
	<?php elseif ($meeting->status == 'ready' || $meeting->status == 'inprogress'): ?>
		<a href='#' class='mb-open-modal open-meeting-monitor an-btn an-btn-primary<?php echo ($is_owner ? '' : ' hidden')?>'
			data-modal-id="meeting-monitor-modal"
			data-url="<?php e(site_url('meeting/monitor/' . $meeting_key)) ?>" 
			data-modal-dialog-class="modal-80"
		>
			<i class="ion-ios-play"></i> <?php e(lang('st_monitor')); ?>
		</a>
		<a href='#' class='mb-open-modal open-meeting-monitor an-btn an-btn-primary<?php echo (!$is_owner && $meeting->status == 'inprogress'? '' : ' hidden')?>'
			data-modal-id="meeting-monitor-modal"
			data-url="<?php e(site_url('meeting/monitor/' . $meeting_key)) ?>" 
			data-modal-dialog-class="modal-80"
		>
			<i class="ion-ios-play"></i> <?php e(lang('st_join')); ?>
		</a>
	<?php endif; ?>

	<?php if ($meeting->manage_state == 'decide' && $is_owner): ?>

	<a href='#' class='an-btn an-btn-primary mb-open-modal'
		data-modal-id="meeting-decider-modal"
		data-url="<?php e(site_url('meeting/decider/' . $meeting_key)) ?>" 
		data-modal-dialog-class="modal-80"
	><i class="ion-play"></i> <?php echo lang('st_decider')?></a>

	<?php endif; ?>

	<?php if ($meeting->manage_state == 'evaluate' && $evaluated === false): ?>
	<a href='#' id="open-meeting-evaluator" data-is-owner="<?php echo $is_owner == true ? '1' : '0' ?>" class='an-btn an-btn-primary'><i class="ion-play"></i> <?php echo lang('st_evaluator')?></a>
	<?php endif; ?>
</div>

<div class="row">
	<div class="col-md-9">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_detail'))?> </h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block meeting-detail readmore-container">
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_owner'))?></div>
						<div class="col-xs-8 owner"><?php echo display_user($meeting->email, $meeting->first_name, $meeting->last_name, $meeting->avatar); ?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_goal'))?></div>
						<div class="col-xs-8">
							<div class="meeting-goal-container">
								<div class="goal">
									<?php echo $meeting->goal?></div>
								</div>
							</div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_status'))?></div>
						<div class="col-xs-8 status">
							<span class="<?php e($label[$meeting->status])?>" id="meeting-status" data-status="<?php e($meeting->status)?>" data-is-owner="<?php e($is_owner ? 1 : 0)?>"><?php e(lang('st_' . $meeting->status))?></span>
						</div>

					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_point_used')) ?></div>
						<div class="col-xs-8 point-used"><?php e($point_used) ?></div>
					</div>
					<?php if ($meeting->scheduled_start_time): ?>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_scheduled_duration')) ?></div>
						<div class="col-xs-8"><?php echo timespan(strtotime($meeting->scheduled_start_time), strtotime($scheduled_end_time)) ?></div>
					</div>
					<?php endif;?>
					<?php if ($meeting->actual_start_time && $meeting->actual_end_time): ?>
					<div class="row">
						<div class="col-xs-4"><?php e(ucfirst(lang('st_actual_duration')))?></div>
						<div class="col-xs-8"><?php echo timespan(strtotime($meeting->actual_start_time), strtotime($meeting->actual_end_time)) ?></div>
					</div>
					<?php endif;?>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->

		<?php if (! empty($meeting->notes)) : ?>
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_notes_summary'))?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block readmore-container">
					<div class="an-input-group meeting-notes">
						<?php echo nl2br($meeting->notes) ?>
					</div>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end .AN-SINGLE-COMPONENT  -->
		<?php endif; ?>

		<!-- Agendas -->
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_agendas'))?></h6>
				</div>
			<div id="agenda-list" class="an-component-body">
				<div class="an-helper-block">
					<div class="an-scrollable-x">
						<table class="table table-striped table-detail-agenda">
							<thead>
								<tr>
									<th><?php e(lang('st_key'))?></th>
									<th><?php e(lang('st_name'))?></th>
									<th><?php e(lang('st_description'))?></th>
									<th><?php e(lang('st_assignee'))?></th>
									<th class="text-center"><?php e(lang('st_status'))?></th>
									<?php if ($meeting->status == 'finished' || $meeting->status == 'resolved') : ?>
									<th class="text-center"><?php e(lang('st_confirmation_status'))?></th>
									<?php endif ?>
								</tr>
							</thead>
							<tbody>
								<?php if($agendas): foreach ($agendas as $agenda) : ?>
								<tr data-agenda-id="<?php e($agenda->agenda_id) ?>" data-confirm-status="<?php e($agenda->confirm_status) ?>">
									<td class='basis-10'><?php e($agenda->agenda_key) //anchor(site_url('agenda/' . $agenda->agenda_key), $agenda->agenda_key)?></td>
									<td class='basis-15'><?php e($agenda->name) //anchor(site_url('agenda/' . $agenda->agenda_key), $agenda->name)?></td>
									<td class='basis-20'><?php echo word_limiter($agenda->description, 20)?></td>
									<td class='basis-20'>
										<?php if ($agenda->members) {
											foreach ($agenda->members as $member) {
												echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar, true) . ' ';
											}
										} ?>
									</td>
									<td class='basis-10 agenda-status text-center'>
										<span class="label label-bordered label-<?php e($agenda->status)?>"><?php e(lang('st_' . $agenda->status))?></span>
									</td>
									<?php if ($meeting->status == 'finished' || $meeting->status == 'resolved') : ?>
									<td class='basis-10 agenda-status text-center'>
										<span class="label label-bordered label-<?php e($agenda->confirm_status) ?>"><?php e(lang('st_' . $agenda->confirm_status))?></span>
									</td>
									<?php endif ?>
								</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>

					<?php if ($meeting->status == 'open'): ?>
					<button class="an-btn an-btn-primary" data-toggle="modal" data-add-agenda-url="<?php echo site_url('agenda/create/' . $meeting_key) ?>" data-target="#bigModal" data-backdrop="static" id="add-agenda"><?php echo '<i class="ion-android-add"></i> ' . lang('st_add_agenda')?></button>
					<?php endif; ?>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end AGENDAS -->

		<!-- Homeworks -->
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('hw_homework'))?></h6>
				</div>
			<div id="homework-list" class="an-component-body">
				<div class="an-helper-block">
					<div class="an-scrollable-x">
						<table class="table table-striped table-detail-homework">
							<thead>
								<tr>
									<th><?php e(lang('hw_name'))?></th>
									<th><?php e(lang('hw_description'))?></th>
									<th><?php e(lang('hw_member'))?></th>
									<th><?php e(lang('hw_time_spent'))?></th>
									<th class="text-center"><?php e(lang('hw_status'))?></th>
								</tr>
							</thead>
							<tbody>
								<?php if($homeworks): foreach ($homeworks as $homework) : ?>
								<tr data-homework-id="<?php e($homework->homework_id) ?>">
									<td class='basis-15'><?php e($homework->name) ?></td>
									<td class='basis-20'><?php echo word_limiter($homework->description, 20)?></td>
									<td class='basis-20'>
										<?php if ($homework->members) {
											foreach ($homework->members as $member) {
												echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar, true) . ' ';
											}
										} ?>
									</td>
									<td class='basis-20'><?php echo $homework->time_spent ?></td>
									<td class='basis-10 homework-status text-center'>
										<span class="label label-bordered label-<?php e($homework->status)?>"><?php e(lang('hw_' . $homework->status))?></span>
									</td>
								</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>

					<?php if ($meeting->status == 'open'): ?>
					<button class="an-btn an-btn-primary mb-open-modal" data-modal-id="create-homework-modal" data-url="<?php echo site_url('homework/create/' . $meeting->meeting_key) ?>" ><?php echo '<i class="ion-android-add"></i> ' . lang('hw_add_homework')?></button>
					<?php endif; ?>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end Homeworks -->
	</div>

	<!-- Columns right -->
	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_resource'))?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block">
					<div class="an-input-group">
						<ul id="meeting-resource" class="list-unstyled list-member">
							<?php if ($invited_members) { foreach ($invited_members as $user) { ?>
							<li>
								<?php echo display_user($user['email'], $user['first_name'], $user['last_name'], $user['avatar']); ?>

								<span class="badge badge-<?php e($user['cost_of_time'])?> badge-bordered pull-right"><?php e($user['cost_of_time_name'])?></span>
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
				<div class="an-helper-block meeting-detail">
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
					<?php if ($meeting->actual_start_time): ?>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_actual_start_time'))?></div>
						<div class="col-xs-7"><?php e(display_time($meeting->actual_start_time)); ?></div>
					</div>

					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_actual_end_time'))?></div>
						<?php if ($meeting->status == 'inprogress') : ?>
						<div class="col-xs-7"><?php e(lang('st_actual_end_time_still_inprogress')); ?></div>
						<?php else: ?>
						<div class="col-xs-7"><?php e(display_time($meeting->actual_start_time)); ?></div>
						<?php endif; ?>
					</div>
					<hr/>
					<?php endif; ?>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_created'))?></div>
						<div class="col-xs-7"><?php e(display_time($meeting->created_on)); ?></div>
					</div>
					<div class="row">
						<div class="col-xs-5"><?php e(lang('st_updated'))?></div>
						<div class="col-xs-7"><?php e(display_time($meeting->modified_on)); ?></div>
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

<div id="meeting-decider" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="create-meeting" class="modal fade" tabindex="-1" role="dialog">
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

<div id="resolve-agenda" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal modal-monitor-evaluator fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content" style="overflow: hidden;">
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

<script type="text" id="agenda-row">
	<tr data-agenda-id="{{:agenda_id}}" data-confirm-status="">
		<td class="basis-10">{{:agenda_key}}</td>
		<td class="basis-15">{{:name}}</td>
		<td class="basis-20">{{:description}}</td>
		<td class="basis-20">
			{{for assignees}}
				{{:html}}
			{{/for}}
		</td>
		<td class="basis-10 agenda-status text-center">
			<span class="label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</td>
	</tr>
</script>

<script type="text" id="homework-row">
	<tr data-homework-id="{{:homework_id}}">
		<td class='basis-15'>{{:name}}</td>
		<td class='basis-20'>{{:short_description}}</td>
		<td class='basis-20'>
			{{for members}}
				{{:html}}
			{{/for}}
		</td>
		<td class='basis-20'>{{:time_spent}}</td>
		<td class='basis-10 homework-status text-center'>
			<span class="label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</td>
	</tr>
</script>

<script id="monitor-homework-row" type="text">
	<tr id="homework-{{:homework_id}}" data-homework-id="{{:homework_id}}" class="homework">
		<td class="name"><a href="<?php echo site_url('/homework/') ?>{{:homework_id}}" target="_blank">{{:name}}</a></td>
		<td>
			<a href="#" class="description" 
			data-type="textarea" 
			data-name="description" 
			data-pk="{{:homework_id}}" 
			data-url="<?php echo site_url('homework/ajax_edit') ?>" 
			data-value="{{:description}}" 
			data-emptytext="<?php e(lang('hw_no_description')) ?>" 
			data-emptyclass="text-muted">{{:short_description}}</a>
		</td>
		<td>
			{{for members}}
				{{:html}}
			{{/for}}
		<td>
			<a href="#" class="time-spent" 
			data-type="text" 
			data-tpl="<input type='number' meeting='0.01'>" 
			data-name="time_spent" 
			data-pk="{{:homework_id}}" 
			data-url="<?php echo site_url('homework/ajax_edit') ?>" 
			data-emptytext="<i class='ion-edit'></i>" 
			data-emptyclass="text-muted">{{:time_spent}}</a>
		</td>
		<td class="status">
			<!-- Update homework status button -->
			<div class="btn-group">
				<button type="button" class="btn btn-status label-open"><?php e(lang('hw_open')) ?></button>
				<button type="button" class="btn dropdown-toggle label-open" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					<li><a href="#" class="btn-update-homework-status hidden" data-pk="{{:homework_id}}" data-value="open"><?php e(lang('hw_open')) ?></a></li>
					<li><a href="#" class="btn-update-homework-status " data-pk="{{:homework_id}}" data-value="done"><?php e(lang('hw_done')) ?></a></li>
					<li><a href="#" class="btn-update-homework-status " data-pk="{{:homework_id}}" data-value="undone"><?php e(lang('hw_undone')) ?></a></li>
				</ul>
			</div>
		</td>
	</tr>
</script>