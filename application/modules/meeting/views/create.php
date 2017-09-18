<?php 
$times = [
	'1' => '1 minute',
	'5' => '5 minutes',
	'10' => '10 minutes',
	'15' => '15 minutes',
	'30' => '30 minutes',
	'60' => '1 hour',
	'120' => '2 hours',
	'180' => '3 hours',
	'300' => '5 hours',
	'480' => '8 hours',
	'other' => 'Input manually',
]
?>

	<div class="<?php echo $this->input->is_ajax_request() ? '' : 'an-content-body'?>">

		<?php if ($this->input->is_ajax_request()): ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			<h4 class="modal-title"><?php e(lang('st_create_meeting'))?></h4>
		</div> <!-- end MODAL-HEADER -->
		<?php else: ?>
		<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
			<div class="an-page-title">
			<h2><?php e(lang('st_create_meeting'))?></h2>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
		<?php endif; ?>

		<?php echo form_open($this->uri->uri_string() . '?' . $_SERVER['QUERY_STRING'], ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '', 'id' => 'create-meeting']) ?>

		<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
				<?php if (is_array($open_agendas)): ?>
					<?php if (count($open_agendas) == 1): ?>
					<p class="an-small-doc-block">
						<?php echo sprintf(
							lang('st_agenda_x_was_place_in_open_parking_plot'), 
							$open_agendas[0]->name, 
							word_limiter($open_agendas[0]->description, 10)
						) ?>
					</p>
					<?php else: ?>
					<div class="an-small-doc-block">
						<p><?php e(lang('st_there_agendas_were_placed_in_open_parking_lot'));?></p>
						<ul>
							<?php foreach ($open_agendas as $agenda): ?>
							<li><?php echo $agenda->name . ' - ' . word_limiter($agenda->description, 10)?></li>
							<?php endforeach;?>
						</ul>
						<?php e(lang('st_please_create_a_new_meeting_to_finish_it')); ?>
					</div>
					<?php endif; ?>
				<?php endif; ?>
				<?php echo mb_form_input('text', 'name', lang('st_name'), true) ?>
				<?php echo mb_form_input('text', 'owner_id', lang('st_owner'), true, '', 'owner-id an-tags-input', '', lang('st_select_team_member')) ?>
				<?php echo mb_form_input('text', 'team', lang('st_resource'), true, '', 'team select-member an-tags-input', '', lang('st_add_team_member')) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="goal" class="pull-right"><?php e(lang('st_goal')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<textarea name="goal" class="an-form-control"><?php echo set_value('goal') ?></textarea> 
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_in')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-5">
								<select id="meeting-in" class="an-form-control" name="meeting_in">
								<?php foreach ($times as $in => $label) : ?>
									<option value="<?php echo $in ?>"
										<?php if(set_value('in') == $in || ($in == 'other' && ! empty(set_value('in')) && ! in_array(set_value('in'), array_keys($times))) || (empty(set_value('in')) && $in == 60)) echo 'selected' ?>
									><?php echo $label ?></option>
								<?php endforeach ?>
								</select>
							</div>
							<div class="col-md-5">
								<input type="number" style="display: none;" name="in" id="in" class="an-form-control<?php e(iif( form_error('in') , ' danger')) ?>" value="<?php e(set_value('in')) ?>" step="0.1">
							</div>
							<div class="col-md-2" style="display: none; vertical-align: middle" id="in-unit">
								<?php e(lang('st_minutes'))?>
							</div>
						</div>
					</div>
				</div>
				<?php if (! empty($this->input->get('recurring'))) : ?>
				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_repeat')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-9 col-sm-12">
								<input type="checkbox" style="margin-top: 10px;" name="repeat" value="1" <?php echo set_value('repeat') == 1 ? 'checked' : '' ?> />
								<input type="text" class="hidden" name="readable" value="<?php echo set_value('readable') ?>" />
								<textarea name="rrule_recurring" class="hidden"><?php echo set_value('rrule_recurring') ?></textarea> 
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_current_state')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-9 col-sm-12">
								<span id="readble" style="padding-top: 5px;display: inline-block;"><?php echo set_value('readable') ?></span>
							</div>
						</div>
					</div>
				</div>
				<script>
				$('textarea[name=recurring]').change();
				</script>
				<?php endif ?>
				<?php if (empty($this->input->get('on_calendar'))) : ?>
				<input type="text" style="display: none;" name="scheduled_start_time" id="meeting-scheduled-start-time" value="<?php echo set_value('scheduled_start_time') ?>">
				<?php endif ?>
		</div>

		<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('st_create'))?></button>
			<a href="#" class="an-btn an-btn-danger-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('st_cancel'))?></a>
		</div>

		<?php echo form_close(); ?>
	</div>

<?php
if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('create_js', [
		'project_members' => $project_members,
		'default_cost_of_time' => $default_cost_of_time,
		'default_cost_of_time_name' => $default_cost_of_time_name,
	], true) . '</script>';
}
?>