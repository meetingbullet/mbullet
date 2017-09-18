<?php

$scheduled_start_time = null;
$is_owner = $meeting->owner_id == $current_user->user_id;

if ($meeting->scheduled_start_time) {
	$scheduled_start_time = strtotime($meeting->scheduled_start_time);
	$scheduled_end_time = strtotime('+' . $meeting->in . ' ' . $meeting->in_type, $scheduled_start_time);

	$scheduled_start_time = date('Y-m-d H:i:s', $scheduled_start_time);
	$scheduled_end_time = date('Y-m-d H:i:s', $scheduled_end_time);
}

if (empty($meeting->is_private)) {
	$action_key = explode('-', $meeting_key);
	$cost_code = $action_key['0'];
	$action_key = $action_key['0'] . '-' . $action_key[1];
	$members = array_column($invited_members, 'user_id');
	$is_member = in_array($current_user->user_id, $members);
	if ($is_member && $is_owner) {
		$is_member = false;
	}
}

$hw_status = ['open', 'done', 'undone'];
?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h2 id="meeting-name"><?php e($meeting->name)?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->

<div class="btn-block">
	<?php echo anchor(empty($meeting->is_private) ? site_url('dashboard#project/' . $cost_code) : site_url('dashboard#unspecified-meetings'), '<i class="ion-android-arrow-back"></i> ' . lang('st_back'), ['class' => 'an-btn an-btn-primary' ]) ?>
	<a href='#' id="edit-meeting" class='an-btn an-btn-primary'><i class="ion-edit"></i> <?php echo lang('st_edit')?></a>
	<?php if (empty($meeting->is_private)) : ?>
		<?php if ($meeting->status == 'open'): ?>
			<a href='#' class='mb-open-modal open-meeting-monitor an-btn an-btn-danger meeting-open<?php echo ($is_owner ? '' : ' hidden')?>'
				data-modal-id="meeting-monitor-modal"
				data-url="<?php e(site_url('meeting/monitor/' . $meeting_key)) ?>" 
				data-modal-dialog-class="modal-80"
			>
				<i class="ion-ios-play"></i> <?php e(lang('st_set_up')); ?>
			</a>
		<?php elseif ($meeting->status == 'ready' || $meeting->status == 'inprogress'): ?>
			<a href='#' class='mb-open-modal open-meeting-monitor an-btn an-btn-danger<?php echo ($is_owner ? '' : ' hidden')?>'
				data-modal-id="meeting-monitor-modal"
				data-url="<?php e(site_url('meeting/monitor/' . $meeting_key)) ?>" 
				data-modal-dialog-class="modal-80"
			>
				<i class="ion-ios-play"></i> <?php e(lang('st_monitor')); ?>
			</a>
			<a href='#' class='mb-open-modal open-meeting-monitor an-btn an-btn-danger<?php echo (!$is_owner && $meeting->status == 'inprogress'? '' : ' hidden')?>'
				data-modal-id="meeting-monitor-modal"
				data-url="<?php e(site_url('meeting/monitor/' . $meeting_key)) ?>" 
				data-modal-dialog-class="modal-80"
			>
				<i class="ion-ios-play"></i> <?php e(lang('st_join')); ?>
			</a>
		<?php endif; ?>

		<?php if ($meeting->manage_state == 'decide' && $is_owner): ?>

		<a href='#' class='an-btn an-btn-danger mb-open-modal'
			data-modal-id="meeting-decider-modal"
			data-url="<?php e(site_url('meeting/decider/' . $meeting_key)) ?>" 
			data-modal-dialog-class="modal-80"
		><i class="ion-play"></i> <?php echo lang('st_decider')?></a>

		<?php endif; ?>

		<?php if ($meeting->manage_state == 'evaluate' && $evaluated === false && (($is_member /*&& ! empty($owner_evaluated)*/) || $is_owner)) : ?>
		<a href='#' id="open-meeting-evaluator" data-is-owner="<?php echo $is_owner == true ? '1' : '0' ?>" class='an-btn an-btn-danger'><i class="ion-play"></i> <?php echo lang('st_evaluator')?></a>
		<?php endif; ?>
	<?php endif ?>
</div>

<div class="row">
	<div class="col-md-9">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_goal'))?> </h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block">
					<div class="meeting-goal-container readmore-container">
						<div class="detail-goal">
							<?php echo $meeting->goal?>
						</div>
					</div>
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
					<div class="an-input-group meeting-notes readmore-container">
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
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php if($agendas): foreach ($agendas as $agenda) : ?>
								<tr data-agenda-id="<?php e($agenda->agenda_id) ?>" data-confirm-status="<?php e($agenda->confirm_status) ?>" class="<?php if ($meeting->status == 'open') echo 'editable' ?>">
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
									<td class='basis-10 text-right'><?php if ($meeting->status == 'open' || $meeting->status == 'ready') : ?><i class="ion-close-circled close-btn"></i><?php endif ?></td>
								</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>

					<?php if ($meeting->status == 'open' || $meeting->status == 'ready'): ?>
					<button class="an-btn an-btn-primary" data-toggle="modal" data-add-agenda-url="<?php echo site_url('agenda/create/' . (empty($meeting->is_private) ? $meeting_key : $meeting->meeting_id)) ?>" data-target="#bigModal" data-backdrop="static" id="add-agenda"><?php echo '<i class="ion-android-add"></i> ' . lang('st_add_agenda')?></button>
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
					<div class="">
						<table class="table table-striped table-detail-homework">
							<thead>
								<tr>
									<th><?php e(lang('hw_name'))?></th>
									<th><?php e(lang('hw_description'))?></th>
									<th><?php e(lang('hw_member'))?></th>
									<th class='text-center'><?php e(lang('hw_time_spent'))?></th>
									<th><?php e(lang('hw_attachment'))?></th>
									<th class="text-center"><?php e(lang('hw_status'))?></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php if($homeworks): foreach ($homeworks as $homework) : $can_edit = $current_user->user_id == $homework->created_by || in_array($current_user->user_id, array_column($homework->members, 'user_id'));?>
								<tr data-homework-id="<?php e($homework->homework_id) ?>" class="<?php if ($meeting->status == 'open' || $meeting->status == 'ready') echo 'editable' ?>">
									<td class='basis-15'><?php e($homework->name) ?></td>
									<td class='basis-20'><?php echo word_limiter($homework->description, 20)?></td>
									<td class='basis-20'>
										<?php if ($homework->members) {
											foreach ($homework->members as $member) {
												echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar, true) . ' ';
											}
										} ?>
									</td>
									<td class='basis-20 text-center'><?php echo $homework->time_spent ?></td>
									<td>
										<?php if ($homework->attachments): ?>
										<div class="attachment">
											<?php foreach ($homework->attachments as $att): ?>
											<a href="<?php echo $att->url ?>" target="_blank">
												<span class="icon">
													<?php if ($att->favicon): ?>
													<img src="<?php echo $att->favicon ?>" data-toggle="tooltip" alt="[A]" title="<?php echo $att->title ? $att->title : $att->url ?>">
													<?php else: ?>
													<i class="icon-file" data-toggle="tooltip" title="<?php echo $att->title ? $att->title : $att->url ?>"></i>
													<?php endif; ?>
												</span>
											</a>
											<?php endforeach; ?>
										</div>
										<?php endif; ?>
									</td>
									<td class='basis-10 homework-status text-center'>
										<!--span class="label label-bordered label-<?php e($homework->status)?>"><?php e(lang('hw_' . $homework->status))?></span-->
										<!-- Update homework status button -->
										<div class="btn-group">
											<button type="button" class="btn btn-status label-<?php echo $homework->status ?>"><?php e(lang('hw_' . $homework->status)) ?></button>
											<?php if ($can_edit) : ?>
											<button type="button" class="btn dropdown-toggle label-<?php echo $homework->status ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
												<span class="caret"></span>
											</button>
											<ul class="dropdown-menu">
											<?php $temp = $hw_status; for ($i = 0; $i < count($temp); $i++) : ?>
												<?php if ($temp[$i] == $homework->status) : ?>
												<li><a href="#" class="btn-update-homework-status hidden" data-pk="<?php echo $homework->homework_id ?>" data-value="<?php echo $temp[$i] ?>"><?php e(lang('hw_' . $temp[$i])) ?></a></li>
												<?php elseif ($i <= count($hw_status)) : $temp[] = $temp[$i]; ?>
												<?php else : ?>
												<li><a href="#" class="btn-update-homework-status" data-pk="<?php echo $homework->homework_id ?>" data-value="<?php echo $temp[$i] ?>"><?php e(lang('hw_' . $temp[$i])) ?></a></li>
												<?php endif ?>
											<?php endfor ?>
											<?php endif ?>
											</ul>
										</div>
									</td>
									<td class='basis-10 text-right'><?php if ($meeting->status == 'open' || $meeting->status == 'ready') : ?><i class="ion-close-circled close-btn"></i><?php endif ?></td>
								</tr>
								<?php endforeach; endif; ?>
							</tbody>
						</table>
					</div>

					<?php if ($meeting->status == 'open' || $meeting->status == 'ready'): ?>
					<button class="an-btn an-btn-primary mb-open-modal" 
						data-modal-id="create-homework-modal" 
						data-url="<?php echo site_url('homework/create/' . (empty($meeting->is_private) ? $meeting_key : $meeting->meeting_id)) ?>" >
						<?php echo '<i class="ion-android-add"></i> ' . lang('hw_add_homework')?>
					</button>
					<?php endif; ?>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div> <!-- end Homeworks -->
	</div>

	<!-- Columns right -->
	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_detail'))?> </h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block meeting-detail">
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_owner'))?></div>
						<div class="col-xs-8 owner"><?php echo display_user($meeting->email, $meeting->first_name, $meeting->last_name, $meeting->avatar); ?></div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_status'))?></div>
						<div class="col-xs-8 status">
							<span class="label label-bordered label-<?php e($meeting->status)?>" id="meeting-status" data-status="<?php e($meeting->status)?>" data-is-owner="<?php e($is_owner ? 1 : 0)?>"><?php e(lang('st_' . $meeting->status))?></span>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-4"><?php e(lang('st_point_used')) ?></div>
						<div class="col-xs-8 point-used"><?php e(empty($meeting->is_private) ? $point_used : 'N/A') ?></div>
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

		<!-- Resource -->
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
								<?php if (! empty($meeting->is_private)) : ?>
									<?php echo display_user($user['email'], ! empty($user['first_name']) ? $user['first_name'] : $user['email'], ! empty($user['last_name']) ? $user['last_name'] : null, ! empty($user['avatar']) ? $user['avatar'] : null); ?>

									<?php if (! empty($user['user_id']) && $user['user_id'] == $current_user->user_id) : ?>
									<i class="decision ion-checkmark-circled"></i>
									<?php else : ?>
									<i class="decision ion-help-circled"></i>
									<?php endif ?>

									<span class="badge badge-bordered pull-right">N/A</span>
								<?php else : ?>
									<?php echo display_user($user['invite_email'], $user['first_name'] ? $user['first_name'] : $user['invite_email'], $user['last_name'] ? $user['last_name'] : null, $user['avatar'] ? $user['avatar'] : null); ?>

									<?php if ($user['status'] == 'ACCEPTED') : ?>
									<i class="decision ion-checkmark-circled"></i>
									<?php elseif ($user['status'] == 'DECLINED') : ?>
									<i class="decision ion-close-circled"></i>
									<?php elseif ($user['status'] == 'TENTATIVE') : ?>
									<i class="decision ion-help-circled"></i>
									<?php elseif ($user['status'] == 'NEEDS-ACTION') : ?>
									<i class="decision ion-help-circled"></i>
									<?php endif ?>

									<span class="badge badge-<?php e($user['cost_of_time'])?> badge-bordered pull-right"><?php e($user['cost_of_time_name'])?></span>
								<?php endif ?>
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
<?php if (! empty($chosen_agenda)) : ?>
<div id="agenda-modal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?php e($chosen_agenda->name) ?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-12">
						<div class="row" style="padding-bottom: 10px;">
							<div class="col-xs-4"><label><?php e(lang('st_key')) ?>:</label></div>
							<div class="col-xs-8"><?php e($agenda->agenda_key) ?></div>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="row" style="padding-bottom: 10px;">
							<div class="col-xs-4"><label><?php e(lang('st_description')) ?>:</label></div>
							<div class="col-xs-8"><?php echo word_limiter($agenda->description, 20)?></div>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="row" style="padding-bottom: 10px;">
							<div class="col-xs-4"><label><?php e(lang('st_assignee')) ?>:</label></div>
							<div class="col-xs-8">
							<?php if ($agenda->members) {
								foreach ($agenda->members as $member) {
									echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar, true) . ' ';
								}
							} ?>
							</div>
						</div>
					</div>
					<div class="col-xs-12">
						<div class="row">
							<div class="col-xs-4"><label><?php e(lang('st_status')) ?>:</label></div>
							<div class="col-xs-8"><span class="label label-bordered label-<?php e($agenda->status)?>"><?php e(lang('st_' . $agenda->status))?></span></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php endif ?>

<div id="current-data" style="display: none;"><?php echo json_encode(empty($meeting->is_private) ? [$evaluated, $invited_members , $point_used, $meeting, $agendas, $homeworks] : [$invited_members, $meeting, $agendas, $homeworks]); ?></div>

<div class="refresh-asking" style="display: none;">
	<?php echo lang('refresh_asking') ?>
</div>

<?php if (empty($meeting->is_private)) : ?>
<script type="text" id="agenda-row">
	<tr data-agenda-id="{{:agenda_id}}" data-confirm-status="" class="<?php if ($meeting->status == 'open' || $meeting->status == 'ready') echo 'editable' ?>">
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
		<td class='basis-10 text-right'><?php if ($meeting->status == 'open' || $meeting->status == 'ready') : ?><i class="ion-close-circled close-btn"></i><?php endif ?></td>
	</tr>
</script>

<script type="text" id="homework-row">
	<tr data-homework-id="{{:homework_id}}" class="<?php if ($meeting->status == 'open' || $meeting->status == 'ready') echo 'editable' ?>">
		<td class='basis-15'>{{:name}}</td>
		<td class='basis-20'>{{:short_description}}</td>
		<td class='basis-20'>
			{{for members}}
				{{:html}}
			{{/for}}
		</td>
		<td class='basis-20 text-center'>{{:time_spent}}</td>
		<td>
			{{if attachments}}
				<div class="attachment">
					{{for attachments}}
						{{:html}}
					{{/for}}
				</div>
			{{/if}}
		</td>
		<td class='basis-10 homework-status text-center'>
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
		<td class='basis-10 text-right'><?php if ($meeting->status == 'open' || $meeting->status == 'ready') : ?><i class="ion-close-circled close-btn"></i><?php endif ?></td>
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
		<td class='text-center'>
			<a href="#" class="time-spent" 
			data-type="text" 
			data-tpl="<input type='number' meeting='0.01'>" 
			data-name="time_spent" 
			data-pk="{{:homework_id}}" 
			data-url="<?php echo site_url('homework/ajax_edit') ?>" 
			data-emptytext="<i class='ion-edit'></i>" 
			data-emptyclass="text-muted">{{:time_spent}}</a>
		</td>
		<td>
			{{if attachments}}
				<div class="attachment">
					{{for attachments}}
						{{:html}}
					{{/for}}
				</div>
			{{/if}}
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
<?php else : ?>
<script type="text" id="agenda-row">
	<tr data-agenda-id="{{:agenda_id}}" data-confirm-status="" class="<?php if ($meeting->status == 'open') echo 'editable' ?>">
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
		<td class='basis-10 text-right'><?php if ($meeting->status == 'open') : ?><i class="ion-close-circled close-btn"></i><?php endif ?></td>
	</tr>
</script>

<script type="text" id="homework-row">
	<tr data-homework-id="{{:homework_id}}" class="<?php if ($meeting->status == 'open') echo 'editable' ?>">
		<td class='basis-15'>{{:name}}</td>
		<td class='basis-20'>{{:short_description}}</td>
		<td class='basis-20'>
			{{for members}}
				{{:html}}
			{{/for}}
		</td>
		<td class='basis-20 text-center'>{{:time_spent}}</td>
		<td>
			{{if attachments}}
				<div class="attachment">
					{{for attachments}}
						{{:html}}
					{{/for}}
				</div>
			{{/if}}
		</td>
		<td class='basis-10 homework-status text-center'>
			<!--span class="label label-bordered label-{{:status}}">{{:lang_status}}</span-->
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
		<td class='basis-10 text-right'><?php if ($meeting->status == 'open') : ?><i class="ion-close-circled close-btn"></i><?php endif ?></td>
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
		<td class='text-center'>
			<a href="#" class="time-spent" 
			data-type="text" 
			data-tpl="<input type='number' meeting='0.01'>" 
			data-name="time_spent" 
			data-pk="{{:homework_id}}" 
			data-url="<?php echo site_url('homework/ajax_edit') ?>" 
			data-emptytext="<i class='ion-edit'></i>" 
			data-emptyclass="text-muted">{{:time_spent}}</a>
		</td>
		<td>
			{{if attachments}}
				<div class="attachment">
					{{for attachments}}
						{{:html}}
					{{/for}}
				</div>
			{{/if}}
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
<?php endif ?>