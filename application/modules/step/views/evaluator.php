<?php

if ($step->scheduled_start_time) {
	// Fix add StrToTime with Float number
	if ( (int) $step->in !== $step->in ) {
		switch ($step->in_type) {
			case 'weeks':
				$step->in *= 7;
			case 'days':
				$step->in *= 24;
			case 'hours':
				$step->in *= 60;
			case 'minutes':
				$step->in *= 60;
		}
	}

	$scheduled_start_time = strtotime($step->scheduled_start_time);
	$scheduled_end_time = strtotime('+' . $step->in . ' seconds', $scheduled_start_time);
	$step->in = round( $step->in / 60, 2);
	$step->in_type = 'minutes';

	$scheduled_start_time = date('Y-m-d H:i:s', $scheduled_start_time);
	$scheduled_end_time = date('Y-m-d H:i:s', $scheduled_end_time);
}
?>
<?php echo form_open('', ['class' => 'form-inline form-ajax form-step-schedule']) ?>
<div style="display:none" class="rating">
	<input type="radio" id="star5" value="5" /><label class = "full" for="star5" title="5 stars"></label>
	<input type="radio" id="star4" value="4" /><label class = "full" for="star4" title="4 stars"></label>
	<input type="radio" id="star3" value="3" /><label class = "full" for="star3" title="3 stars"></label>
	<input type="radio" id="star2" value="2" /><label class = "full" for="star2" title="2 stars"></label>
	<input type="radio" id="star1" value="1" /><label class = "full" for="star1" title="1 star"></label>
</div>
<div class="step-monitor">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_step_evaluator'))?></h4>
	</div> <!-- end MODAL-HEADER -->

	<div class="an-body-topbar">
		<div class="an-page-title">
			<div class="an-bootstrap-custom-tab">
				<h2><?php e($step->name)?></h2>
			</div>
		</div>
	</div> <!-- end AN-BODY-TOPBAR -->

	<div class="col-md-5">
		<div class="an-single-component">
			<div class="an-component-body an-helper-block">
				<table class="table table-striped table-step-time">
					<thead>
						<tr>
							<th></th>
							<th class="text-center"><?php e(lang('st_scheduled'))?></th>
							<th class="text-center"><?php e(lang('st_actual'))?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><?php echo lang('st_start_time') ?></strong></td>
							<td class="text-center"><?php e(display_time($scheduled_start_time)) ?></td>
							<td class="text-center"><?php e(display_time($step->actual_start_time)) ?></td>
						</tr>
						<tr>
							<td><strong><?php echo lang('st_end_time') ?></strong></td>
							<td class="text-center"><?php e(display_time($scheduled_end_time)) ?></td>
							<td class="text-center"><?php e(display_time($step->actual_end_time)) ?></td>
						</tr>
						<tr>
							<td><strong><?php echo lang('st_elapsed_time') ?></strong></td>
							<td class="text-center"><?php echo timespan(strtotime($step->scheduled_start_time), strtotime($scheduled_end_time) ) ?></td>
							<td class="text-center"><?php echo timespan(strtotime($step->actual_start_time), strtotime($step->actual_end_time)) ?></td>
						</tr>
					</tbody>
				</table>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>

	<div class="col-md-4">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_goal'))?></h6>
			</div>
			<div class="an-component-body an-helper-block" style="max-height: 300px; overflow-y: auto">
				<?php echo nl2br($step->goal) ?>
			</div>
		</div>
	</div>

	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_attendees'))?></h6>
			</div>
			<div class="an-component-body an-helper-block">
			<?php if (is_array($step->members) && count($step->members) > 0) : ?>
				<?php foreach ($step->members as $member) : ?>
				<div class="attendee">
					<div class="info"><?php echo display_user($member['email'], $member['first_name'], $member['last_name'], $member['avatar']) ?></div>
					<div class="rating">
						<input type="radio" id="star5" name="attendee_rate[<?php echo $member['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $member['user_id'] . ']', 5) ?> value="5" /><label class = "full" for="star5" title="5 stars"></label>
						<!--input type="radio" id="star4half" name="attendee_rate[<?php echo $member->user_id ?>]" value="4.5" /><label class="half" for="star4half" title="4.5 stars"></label-->
						<input type="radio" id="star4" name="attendee_rate[<?php echo $member['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $member['user_id'] . ']', 4) ?> value="4" /><label class = "full" for="star4" title="4 stars"></label>
						<!--input type="radio" id="star3half" name="attendee_rate[<?php echo $member->user_id ?>]" value="3.5" /><label class="half" for="star3half" title="3.5 stars"></label-->
						<input type="radio" id="star3" name="attendee_rate[<?php echo $member['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $member['user_id'] . ']', 3) ?> value="3" /><label class = "full" for="star3" title="3 stars"></label>
						<!--input type="radio" id="star2half" name="attendee_rate[<?php echo $member->user_id ?>]" value="2.5" /><label class="half" for="star2half" title="2.5 stars"></label-->
						<input type="radio" id="star2" name="attendee_rate[<?php echo $member['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $member['user_id'] . ']', 2) ?> value="2" /><label class = "full" for="star2" title="2 stars"></label>
						<!--input type="radio" id="star1half" name="attendee_rate[<?php echo $member->user_id ?>]" value="1.5" /><label class="half" for="star1half" title="1.5 stars"></label-->
						<input type="radio" id="star1" name="attendee_rate[<?php echo $member['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $member['user_id'] . ']', 1) ?> value="1" /><label class = "full" for="star1" title="1 star"></label>
						<!--input type="radio" id="starhalf" name="attendee_rate[<?php echo $member->user_id ?>]" value="0.5" /><label class="half" for="starhalf" title="0.5 stars"></label-->
					</div>
				</div>
				<?php endforeach ?>
			<?php endif ?>
			</div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_agendas'))?></h6>
			</div>
			<div class="an-component-body an-helper-block">
				<table class="table table-striped table-agenda">
					<thead>
						<tr>
							<th><?php e(lang('st_name'))?></th>
							<th class="text-center"><?php e(lang('st_started'))?></th>
							<th class="text-center"><?php e(lang('st_duration'))?></th>
							<th class="text-center"><?php e(lang('st_status'))?></th>
							<th class="text-center"><?php e(lang('st_confirm_status'))?></th>
							<th><?php e(lang('st_rate'))?></th>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($agendas)): foreach ($agendas as $agenda) : ?>
						<tr>
							<td><?php echo anchor(site_url('agenda/' . $agenda->agenda_key), $agenda->name, ['target' => '_blank'])?></td>
							<td class="text-center"><?php e(empty($agenda->started_on) ? '' : display_time($agenda->started_on)) ?></td>
							<td class="text-center"><?php echo timespan(strtotime($agenda->started_on), strtotime($agenda->finished_on)) ?></td>
							<td class="text-center agenda-status">
								<?php if (! empty($agenda->status)) : ?>
								<span class="label label-bordered label-<?php e($agenda->status)?>"><?php e(lang('st_' . $agenda->status))?></span>
								<?php endif ?>
							</td>
							<td class="text-center agenda-status">
								<?php if (! empty($agenda->confirm_status)) : ?>
								<span class="label label-bordered label-<?php e($agenda->confirm_status)?>"><?php e(lang('st_' . $agenda->confirm_status))?></span>
								<?php endif ?>
							</td>
							<td>
								<div class="rating">
									<input type="radio" id="star5" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" <?php echo set_radio('agenda_rate[' . $agenda->agenda_id . ']', 5) ?> value="5" /><label class = "full" for="star5" title="5 stars"></label>
									<!--input type="radio" id="star4half" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" value="4.5" /><label class="half" for="star4half" title="4.5 stars"></label-->
									<input type="radio" id="star4" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" <?php echo set_radio('agenda_rate[' . $agenda->agenda_id . ']', 4) ?> value="4" /><label class = "full" for="star4" title="4 stars"></label>
									<!--input type="radio" id="star3half" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" value="3.5" /><label class="half" for="star3half" title="3.5 stars"></label-->
									<input type="radio" id="star3" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" <?php echo set_radio('agenda_rate[' . $agenda->agenda_id . ']', 3) ?> value="3" /><label class = "full" for="star3" title="3 stars"></label>
									<!--input type="radio" id="star2half" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" value="2.5" /><label class="half" for="star2half" title="2.5 stars"></label-->
									<input type="radio" id="star2" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" <?php echo set_radio('agenda_rate[' . $agenda->agenda_id . ']', 2) ?> value="2" /><label class = "full" for="star2" title="2 stars"></label>
									<!--input type="radio" id="star1half" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" value="1.5" /><label class="half" for="star1half" title="1.5 stars"></label-->
									<input type="radio" id="star1" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" <?php echo set_radio('agenda_rate[' . $agenda->agenda_id . ']', 1) ?> value="1" /><label class = "full" for="star1" title="1 star"></label>
									<!--input type="radio" id="starhalf" name="agenda_rate[<?php echo $agenda->agenda_id ?>]" value="0.5" /><label class="half" for="starhalf" title="0.5 stars"></label-->
								</div>
							</td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>

	<div class="col-md-12">
		<label><?php echo lang('st_notes') ?></label>
		<div class="step-notes-container">
			<div class='step-notes'>
			<?php echo nl2br($step->notes) ?>
			</div>
		</div>
	</div>

	<div class="col-md-12" style="padding-bottom: 30px;">
		<div class="row">
			<div class="col-md-8">
				<label><?php echo lang('st_point_value') ?></label>
				<span><?php echo $point_used ?></span>
			</div>

			<div class="col-md-4">
				<button type="submit" id="submit_evaluator" class="an-btn an-btn-primary pull-right" name="save"><?php echo lang('st_submit') ?></button>
			</div>
		</div>
	</div>
</div>
<?php echo form_close() ?>
<script>
	<?php
	echo $this->load->view('evaluator_js', [
		'step_key' => $step->step_key
	], true);
	?>
</script>