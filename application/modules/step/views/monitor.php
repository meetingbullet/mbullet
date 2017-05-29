<?php
$is_owner = $step->owner_id == $current_user->user_id;
$scheduled_start_time = null;

if ($step->scheduled_start_time) {
	$scheduled_end_time = date_create_from_format('Y-m-d H:i:s', $step->scheduled_start_time);
	$scheduled_end_time->modify('+' . $step->in . ' ' . $step->in_type);

	$scheduled_start_time = display_time($step->scheduled_start_time);
	$scheduled_end_time = display_time($scheduled_end_time->format('Y-m-d H:i:s'));
}

$scheduled_time = $scheduled_start_time ? $scheduled_start_time . ' - ' . $scheduled_end_time : null;

$agenda_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
];
?>
<div data-step-id="<?php e($step->step_id)?>" class="step-monitor" data-is-owner="<?php echo $is_owner ? 1 : 0 ?>">
	<?php if (IS_AJAX): ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_step_monitor'))?></h4>
	</div> <!-- end MODAL-HEADER -->
	<?php endif; ?>

	<?php echo form_open(site_url('step/update_step_schedule'), ['class' => 'form-inline form-step-schedule']) ?>
		<input type="hidden" name="scheduled_start_time"/>

		<div class="topbar">
			<div class="an-page-title">
				<div class="an-bootstrap-custom-tab">
					<h2><?php e($step->name)?></h2>

					<?php if ($step->status != 'open'): ?>
					<h5 class='text-muted'><?php e($scheduled_time)?></h5>
					<?php endif; ?>
				</div>
			</div>
			<div class="pull-right">
				<div class="an-bootstrap-custom-tab">
					<div class="step-time-schedule">
							<input type="hidden" name="step_id" value="<?php e($step->step_id) ?>">

							<h3 id="scheduled-timer" class="step-action hidden" data-now="<?php e($now)?>" data-actual-start-time="<?php echo $step->status == 'inprogress' ? $step->actual_start_time : ''?>"></h3>
							
							<?php if ($is_owner): ?>
							<?php if ($step->status != 'open'): ?>
							<div class="step-action">
								<button type="submit" 
										name='start-step' 
										class="an-btn an-btn-danger btn-start-step<?php echo $step->status == 'open' || $step->status == 'ready' ? '' : ' hidden' ?>">
									<i class="ion-ios-play"></i> <?php e(lang('st_start'))?>
								</button>
								<button class="an-btn an-btn-success btn-finish<?php echo $step->status == 'inprogress' && $is_owner ? '' : ' hidden' ?>" disabled>
									<i class="ion-checkmark"></i> <?php e(lang('st_finish'))?>
								</button>
							</div>
							<?php else: ?>
							<div class="an input-group input-group-schedule <?php echo $step->status == 'open' ? ' input-group-btn-right' : '' ?>">
								<div class="input-group-addon"><i class="ion-android-calendar"></i></div>
								<input type="text" 
										id="datetimepicker1"
										name="scheduled_time" 
										class="form-control an-form-control schedule-time" 
										value="<?php echo $scheduled_start_time ?>" 
										placeholder="<?php e(lang('st_scheduled_start_time'))?>" <?php echo $step->status == 'open' ? '' : 'disabled' ?>/>
								<span class="input-group-btn">
									<button type="submit" 
											name='save-time' 
											class="an-btn an-btn-danger btn-update-step-schedule<?php echo $step->status == 'open' ? '' : ' hidden' ?>">
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

	<div id="step-joiner">
		
	</div>

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
						<td class=""><?php echo anchor(site_url('agenda/' . $agenda->agenda_key), $agenda->name, ['target' => '_blank'])?></td>
						<td class=""><?php echo word_limiter($agenda->description, 24)?></td>
						<td class="">
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

							<input type="number" name="time_assigned" data-agenda-id='<?php e($agenda->agenda_id)?>' class='an-form-control form-td<?php echo ($agenda->time_assigned == NULL && $is_owner ? '' : ' hidden' ) ?>' step="0.01" value="<?php e($agenda->time_assigned)?>"/>
						</td>
						<td class='text-center skip-votes '><?php e($agenda->skip_votes)?></td>
						<td class='agenda-status' <?php echo "data-time-assigned='{$agenda->time_assigned}' " . ($agenda->status == 'inprogress' ? "data-now='{$now}' data-started-on='{$agenda->started_on}'" : '') ?>>
							<span class="label label-bordered <?php e(' label-' . $agenda->status)?>"><?php e(lang('st_' . $agenda->status))?></span>
						</td>

						<?php if ($is_owner): ?>
						<td class='agenda-action '>
							<button class="an-btn an-btn-small an-btn-primary btn-start-agenda<?php e($step->status == 'inprogress' && $agenda->status == 'open' ? '' : ' hidden')?>"<?php e($agenda->time_assigned ? '' : ' disabled')?>>
								<?php e(lang('st_start'))?>
							</button>
							<button class="an-btn an-btn-small an-btn-primary btn-skip<?php e($step->status == 'inprogress' && $agenda->status == 'open' ? '' : ' hidden')?>"><?php e(lang('st_skip'))?></button>
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
	</div>
</div>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('monitor_js', [
		'step_key' => $step_key
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