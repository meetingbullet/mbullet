<div>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e($event->summary)?></h4>
	</div> <!-- end MODAL-HEADER -->

	<?php echo form_open(site_url('meeting/import') . '?' . $_SERVER['QUERY_STRING'] , ['class' => 'form-ajax']) ?>

	<div class='container-fluid modal-body'>
		<div>
			<p><?php echo date("F j, Y - H:i:s", strtotime($this->input->get('start'))) . ' <i class="ion-arrow-right-c"></i> ' . date("F j, Y - H:i:s", strtotime($this->input->get('end'))) ?></p>
		</div>
		<?php if (! empty($event->recurringHumanReadable)) : ?>
		<div>
			<p><?php echo ucfirst($event->recurringHumanReadable) ?></p>
		</div>
		<?php endif ?>
		<?php if (! empty($event->attendees)) : foreach ($event->attendees as $attendee) : ?>
		<div>
			<p>
				<i class="
				<?php
				if ($attendee->responseStatus == 'accepted') {
					echo 'ion-checkmark-circled';
				} elseif ($attendee->responseStatus == 'declined') {
					echo 'ion-close-circled';
				} else {
					echo 'ion-help-circled';
				}
				?>
				"></i>
				<?php echo $attendee->email . ($attendee->organizer == 1 ? ' - Organizer' : ''); ?>
			</p>
		</div>
		<?php endforeach; else : ?>
		<div>
			<p>
				<i class="ion-checkmark-circled"></i>
				<?php echo $event->organizer->email . ' - Organizer'; ?>
			</p>
		</div>
		<?php endif ?>
	</div>

	<div class="modal-footer">
		<input type="hidden" name="import_mode" value="0" />
		<button type="submit" name="save_step_1" id="save_step_1" class="<?php if (! empty($event->recurrence)) echo "mb-open-modal" ?> an-btn an-btn-primary"
		data-is-recurring="<?php echo empty($event->recurrence) ? 0 : 1 ?>"
		<?php
		if (! empty($event->recurrence)) {
			echo "
				data-modal-id=\"import-mode-modal\"
				data-title=\"Convert event to MB meeting\"
				data-content=\"
				<p>Do you want to convert all occurrences of this event, or only the selected occurrence?</p>
				<div class='button-wrapper'><button class='btn btn-info pull-right' id='convert-all'>Convert All</button><button class='btn btn-success pull-right' id='convert-one'>Convert Only This Event</button></div>
				\"
			";
		}
		?>><?php e(lang('st_convert_to_mb'))?></button>
	</div>

	<?php echo form_close(); ?>
</div>
<style>
#import-mode-modal .modal-footer {
	display: none;
}
#import-mode-modal .button-wrapper {
	overflow: hidden;
}
#import-mode-modal .button-wrapper button {
	margin: 10px;
}
</style>