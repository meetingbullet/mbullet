<div>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_scheduled_start_time'))?></h4>
	</div> <!-- end MODAL-HEADER -->

	<div class='container-fluid modal-body'>
		<input type="text" class="an-form-control" value="<?php echo $this->input->get('selected_date') ?>" id="dt-picker"/>
	</div>

	<div class="modal-footer">
		<button type="button" name="save" id="dt-pick" class="an-btn an-btn-primary"><?php e(lang('st_pick'))?></button>
		<button type="button" name="save" id="dt-later" class="an-btn an-btn-danger-transparent"><?php e(lang('st_later'))?></button>
	</div>
</div>
<script>
var selected_date = $('#dt-picker').val();
if (selected_date) {
	$('#dt-picker').datetimepicker({
		sideBySide: true,
		minDate: new Date()
	});

	$('#dt-picker').data("DateTimePicker").date(new Date(selected_date));
} else {
	$('#dt-picker').datetimepicker({
		sideBySide: true,
		minDate: new Date()
	});
}
</script>