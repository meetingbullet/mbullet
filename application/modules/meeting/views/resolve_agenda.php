<?php

?>
<div id="resolve-agenda">
	<?php if (IS_AJAX): ?>
	<div class="modal-header">
		<h4 class="modal-title"><?php e(lang('st_resolve_agenda'))?></h4>
	</div> <!-- end MODAL-HEADER -->
	<?php endif; ?>

	<div class="an-body-topbar">
		<div class="an-helper-block">
			<h4 class=''><?php e(sprintf(lang('st_time_alotted'), $agenda->name))?></h4>
		</div>
	</div>

	<?php echo form_open(site_url('meeting/resolve_agenda/'), ['id' => 'resolve-agenda', 'class' => 'form-inline form-resolve-agenda', 'data-agenda-id' => $agenda_id]) ?>
		<div class="an-helper-block">
				<label for="comment"><?php e(lang('st_comment_topics'))?></label>
				<textarea class='an-form-control' name="comment" rows="6"></textarea>
		</div> <!-- end .an-helper-block -->

		<div class="<?php echo IS_AJAX ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="resolve" class="an-btn an-btn-primary btn-resolve"><?php e(lang('st_resolve'))?></button>
			<button type="submit" name="parking_lot" class="an-btn an-btn-primary-transparent btn-parking-lot"><?php e(lang('st_parking_lot'))?></button>
		</div>
	<?php echo form_close() ?>
</div>