<?php
$owner_email = '';
$user_emails = [];
if (empty($event->attendees)) {
	$event->attendees = [];
}

if (! in_array($current_user->email, $event->attendees)) {
	$event->attendees[] = (object) ['email' => $current_user->email, 'in_mb_system' => true];
}
?>
<div>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e($event->summary)?></h4>
	</div> <!-- end MODAL-HEADER -->

	<?php echo form_open(site_url('meeting/import') . '?' . $_SERVER['QUERY_STRING'] , ['class' => 'form-ajax']) ?>

	<div class='container-fluid modal-body'><?php //dump($this->input->get()); ?>
		<div>
			<p><?php echo date("F j, Y - H:i:s", strtotime($this->input->get('start'))) . ' <i class="ion-arrow-right-c"></i> ' . date("F j, Y - H:i:s", strtotime($this->input->get('end'))) ?></p>
		</div>
		<?php if (! empty($event->recurringHumanReadable)) : ?>
		<div>
			<p><?php echo ucfirst($event->recurringHumanReadable) ?></p>
		</div>
		<?php endif ?>
		<div>
			<label>Project:</label>
			<div class="project-wrapper">
				<select name="project_id" class="an-form-control">
				<?php foreach ($projects as $project) : ?>
				<option value="<?php echo $project->project_id ?>"><?php echo $project->name ?></option>
				<?php endforeach ?>
				</select>
			</div>
		</div>
		<?php foreach ($event->attendees as $attendee) : ?>
		<div class="item <?php echo ! empty($attendee->in_mb_system) ? 'in-system' : '' ?>">
			<p>
				<i class="ion-close-circled dismiss-user"></i>
				<?php 
				echo '<span class="email">' . $attendee->email . '</span>' . ($attendee->email == $current_user->email ? '&nbsp;<span class="owner">(Owner)</span>' : ''); 
				if ($attendee->email == $current_user->email) {
					$owner_email = $attendee->email;
				} else {
					$user_emails[] = $attendee->email;
				}
				?>
			</p>
		</div>
		<?php endforeach ?>
	</div>

	<div class="modal-footer">
		<input type="hidden" name="owner_email" value="<?php echo $owner_email ?>" />
		<input type="hidden" name="user_emails" value="<?php echo implode($user_emails, ',') ?>" />
		<button type="submit" name="save_step_2" id="save_step_2" class="an-btn an-btn-primary"><?php e(lang('st_confirm'))?></button>
	</div>

	<?php echo form_close(); ?>
</div>
<style>
#event-import-modal .dismiss-user {
	color: #eb547c;
	cursor: pointer;
}
#event-import-modal .email:hover {
	font-weight: bold;
	cursor: pointer;
}
#event-import-modal .in-system {
	font-style: italic;
}
</style>