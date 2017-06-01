<?php
$is_homework_editable = $meeting->status == 'open' || $meeting->status == 'ready' || $meeting->status == 'inprogress';
$is_owner = $meeting->owner_id == $current_user->user_id;
$scheduled_start_time = null;

if ($meeting->scheduled_start_time) {
	$scheduled_end_time = date_create_from_format('Y-m-d H:i:s', $meeting->scheduled_start_time);
	$scheduled_end_time->modify('+' . $meeting->in . ' ' . $meeting->in_type);

	$scheduled_start_time = display_time($meeting->scheduled_start_time);
	$scheduled_end_time = display_time($scheduled_end_time->format('Y-m-d H:i:s'));
}

$scheduled_time = $scheduled_start_time ? $scheduled_start_time . ' - ' . $scheduled_end_time : null;

?>
<div data-meeting-id="<?php e($meeting->meeting_id)?>" class="meeting-monitor" data-status="<?php e($meeting->status) ?>" data-is-owner="<?php echo $is_owner ? 1 : 0 ?>">
	<?php if (IS_AJAX): ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_meeting_monitor'))?></h4>
	</div> <!-- end MODAL-HEADER -->
	<?php endif; ?>

	<?php echo form_open(site_url('meeting/update_meeting_schedule'), ['class' => 'form-inline form-meeting-schedule']) ?>
		<input type="hidden" name="scheduled_start_time"/>

		<div class="topbar">
			<div class="an-page-title">
				<div class="an-bootstrap-custom-tab">
					<h2><?php e($meeting->name)?></h2>

					<?php if ($meeting->status != 'open'): ?>
					<h5 class='text-muted'><?php e($scheduled_time)?></h5>
					<?php endif; ?>
				</div>
			</div>
			<div class="pull-right">
				<div class="an-bootstrap-custom-tab">
					<div class="meeting-time-schedule">
							<input type="hidden" name="meeting_id" value="<?php e($meeting->meeting_id) ?>">

							<h3 id="scheduled-timer" class="meeting-action hidden" data-now="<?php e($now)?>" data-actual-start-time="<?php echo $meeting->status == 'inprogress' ? $meeting->actual_start_time : ''?>"></h3>
							
							<?php if ($is_owner): ?>
							<?php if ($meeting->status != 'open'): ?>
							<div class="meeting-action">
								<button type="submit" 
										name='start-meeting' 
										class="an-btn an-btn-danger btn-start-meeting<?php echo $meeting->status == 'open' || $meeting->status == 'ready' ? '' : ' hidden' ?>">
									<i class="ion-ios-play"></i> <?php e(lang('st_start'))?>
								</button>
								<button class="an-btn an-btn-success btn-finish<?php echo $meeting->status == 'inprogress' && $is_owner ? '' : ' hidden' ?>" disabled>
									<i class="ion-checkmark"></i> <?php e(lang('st_finish'))?>
								</button>
							</div>
							<?php else: ?>
							<div class="an input-group input-group-schedule <?php echo $meeting->status == 'open' ? ' input-group-btn-right' : '' ?>">
								<div class="input-group-addon"><i class="ion-android-calendar"></i></div>
								<input type="text" 
										id="datetimepicker1"
										name="scheduled_time" 
										class="form-control an-form-control schedule-time" 
										value="<?php echo $scheduled_start_time ?>" 
										placeholder="<?php e(lang('st_scheduled_start_time'))?>" <?php echo $meeting->status == 'open' ? '' : 'disabled' ?>/>
								<span class="input-group-btn">
									<button type="submit" 
											name='save-time' 
											class="an-btn an-btn-danger btn-update-meeting-schedule<?php echo $meeting->status == 'open' ? '' : ' hidden' ?>">
										<i class="glyphicon glyphicon-floppy-disk"></i> <?php e(lang('st_save'))?>
									</button>
								</span>
							</div>
							<?php endif; ?>
							<?php endif; ?>
					</div>
				</div>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
	<?php echo form_close() ?>

	<div id="meeting-joiner"></div>

	<!-- Agenda -->
	<div class="an-single-component with-shadow">
		<div class="an-component-header">
			<h6><?php e(lang('st_agendas'))?></h6>
			</div>
		<div class="an-component-body an-helper-block">
			<table class="table table-striped table-agenda">
				<thead>
					<tr>
						<th><?php e(lang('st_name'))?></th>
						<th><?php e(lang('st_description'))?></th>
						<th><?php e(lang('st_assignee'))?></th>
						<th class='text-center'><?php e(lang('st_time_assigned_min'))?></th>
						<th class='text-center'><?php e(lang('st_skip_votes'))?></th>
						<th class="basis-30"><?php e(lang('st_status'))?></th>
						<th><?php e(lang('st_action'))?></th>
					</tr>
				</thead>
				<tbody>
					<?php if($agendas): foreach ($agendas as $agenda) : ?>
					<tr id='agenda-<?php e($agenda->agenda_id)?>' data-agenda-id='<?php e($agenda->agenda_id)?>' data-agenda-status='<?php e($agenda->status)?>'>
						<td><?php echo anchor(site_url('agenda/' . $agenda->agenda_key), $agenda->name, ['target' => '_blank'])?></td>
						<td><?php echo word_limiter($agenda->description, 24)?></td>
						<td>
							<?php if ($agenda->members) {
								foreach ($agenda->members as $member) {
									echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar, true) . ' ';
								}
							} ?>
						</td>
						<td class='text-center '>
							<span class="time-assigned">
								<?php e($agenda->time_assigned)?>
							</span>

							<input type="number" name="time_assigned" data-agenda-id='<?php e($agenda->agenda_id)?>' class='an-form-control form-td<?php echo ($agenda->time_assigned == NULL && $is_owner ? '' : ' hidden' ) ?>' meeting="0.01" value="<?php e($agenda->time_assigned)?>"/>
						</td>
						<td class='text-center skip-votes '><?php e($agenda->skip_votes)?></td>
						<td class='agenda-status' <?php echo "data-time-assigned='{$agenda->time_assigned}' " . ($agenda->status == 'inprogress' ? "data-now='{$now}' data-started-on='{$agenda->started_on}'" : '') ?>>
							<span class="label label-bordered <?php e(' label-' . $agenda->status)?>"><?php e(lang('st_' . $agenda->status))?></span>
						</td>

						<?php if ($is_owner): ?>
						<td class='agenda-action '>
							<button class="an-btn an-btn-small an-btn-primary btn-start-agenda<?php e($meeting->status == 'inprogress' && $agenda->status == 'open' ? '' : ' hidden')?>"<?php e($agenda->time_assigned ? '' : ' disabled')?>>
								<?php e(lang('st_start'))?>
							</button>
							<button class="an-btn an-btn-small an-btn-primary btn-skip<?php e($meeting->status == 'inprogress' && $agenda->status == 'open' ? '' : ' hidden')?>"><?php e(lang('st_skip'))?></button>
							<button class="an-btn an-btn-small an-btn-primary btn-jump<?php e($agenda->status == 'inprogress' ? '' : ' hidden')?>"><?php e(lang('st_jump'))?></button>
						</td>
						<?php else: ?>
						<td class=''>
							<?php if ($agenda->voted_skip == 0):?>
							<button class="an-btn an-btn-small an-btn-primary btn-vote-skip <?php echo $agenda->status == 'resolved' || $agenda->status == 'skipped' || $agenda->status == 'jumped' || $agenda->status == 'parking_lot' ? ' hidden' : ''?>"><?php e(lang('st_vote_skip'))?></button>
							<?php else: ?>
							<button class="an-btn an-btn-small an-btn-primary-transparent" disabled><?php e(lang('st_voted_skip'))?></button>
							<?php endif; ?>
						</td>
						<?php endif; ?>
					</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div> <!-- end .AN-COMPONENT-BODY -->
	</div> <!-- end Agenda -->

	<!-- homeowrk -->
	<div class="an-single-component with-shadow">
		<div class="an-component-header">
			<h6><?php e(lang('hw_homework'))?></h6>

			<div class="pull-right">
				<button class="an-btn an-btn-primary mb-open-modal <?php e($meeting->status != 'open' ? 'hidden' : '') ?>" data-modal-id="create-homework-modal" data-url="<?php echo site_url('homework/create/' . $meeting->meeting_key) ?>" >
					<?php echo '<i class="ion-android-add"></i> ' . lang('hw_add_homework')?>
				</button>
			</div>
			</div>
		<div class="an-component-body an-helper-block">
			<table class="table table-striped table-monitor-homework">
				<thead>
					<tr>
						<th><?php e(lang('hw_name'))?></th>
						<th><?php e(lang('hw_description'))?></th>
						<th><?php e(lang('hw_member'))?></th>
						<th><?php e(lang('hw_time_spent'))?></th>
						<th><?php e(lang('hw_status'))?></th>
					</tr>
				</thead>
				<tbody>
					<?php if($homeworks): foreach ($homeworks as $homework) : 
						$can_edit = $current_user->user_id == $homework->created_by || in_array($current_user->user_id, array_column($homework->members, 'user_id'));
					?>
					<tr id='homework-<?php e($homework->homework_id)?>' 
						data-homework-id='<?php e($homework->homework_id)?>'
						class='homework <?php e($can_edit ? 'can-edit' : '') ?>'>
						<td class='name'><?php echo anchor(site_url('homework/' . $homework->homework_id), $homework->name, ['target' => '_blank'])?></td>
						<td class='description-container'>
							<a href='#' class='description'
								data-type="textarea" 
								data-name="description"
								data-pk="<?php e($homework->homework_id) ?>" 
								data-url="<?php echo site_url('homework/ajax_edit') ?>"
								data-value="<?php e($homework->description) ?>"
								data-emptytext="<?php e(lang('hw_no_description')) ?>"
								data-emptyclass="text-muted"
							><?php echo word_limiter($homework->description, 18)?></a>
						</td>
						<td>
							<?php if ($homework->members) {
								foreach ($homework->members as $member) {
									echo display_user($member->email, $member->first_name, $member->last_name, $member->avatar, true) . ' ';
								}
							} ?>
						</td>
						<td class='time-spent-container'>
							<a href='#' class='time-spent'
								data-type="text" 
								data-tpl="<input type='number' meeting='0.01'>"
								data-name="time_spent"
								data-pk="<?php e($homework->homework_id) ?>" 
								data-url="<?php echo site_url('homework/ajax_edit') ?>"
								data-emptytext="<i class='ion-edit'></i>"
								data-emptyclass="text-muted"
							><?php e($homework->time_spent) ?></a>
						<td class='status-container'>
							<!-- Update homework status button -->
							<div class="btn-group">
								<button type="button" class="btn btn-status label-<?php e($homework->status)?>" data-status="<?php e($homework->status)?>"><?php e(lang('hw_' . $homework->status))?></button>

								<?php if ($is_homework_editable && $can_edit): ?>
								<button type="button" class="btn dropdown-toggle label-<?php e($homework->status)?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li><a href="#" class='btn-update-homework-status <?php e($homework->status == 'done' ? 'hidden' : '')?>' data-pk="<?php e($homework->homework_id) ?>" data-value="done"><?php e(lang('hw_done'))?></a></li>
									<li><a href="#" class='btn-update-homework-status <?php e($homework->status == 'undone' ? 'hidden' : '')?>' data-pk="<?php e($homework->homework_id) ?>" data-value="undone"><?php e(lang('hw_undone'))?></a></li>
								</ul>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div> <!-- end .AN-COMPONENT-BODY -->
	</div> <!-- end Homework -->
	
</div>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('monitor_js', [
		'meeting_key' => $meeting_key,
		'current_user' => $current_user
	], true) . '</script>';
}
?>

<?php if ( ! IS_AJAX): ?>
<!-- Modal -->
<div id="resolve-agenda" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>
<?php endif; ?>