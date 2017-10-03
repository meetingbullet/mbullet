<?php
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title" id="myModaloneLabel"><?php e(lang('ag_edit_agenda')) ?></h4>
</div> <!-- end MODAL-HEADER -->

<?php echo form_open($this->uri->uri_string(), ['class' => 'form-ajax', 'id' => 'create-agenda']) ?>

<div class="container-fluid modal-body">
	<?php echo mb_form_input('text', 'name', lang('ag_name'), true, set_value('name', empty($agenda->name) ? '' : $agenda->name)) ?>
	<?php echo mb_form_input('text', 'description', lang('ag_description'), false, set_value('description', empty($agenda->description) ? '' : $agenda->description)) ?>
	<?php echo mb_form_input('text', 'assignee', lang('ag_assignee'), true, set_value('assignee', empty($agenda_members) ? '' : implode(',' , $agenda_members)), 'team select-member an-tags-input', '', lang('ag_add_team_member')) ?>
</div>

<div class="modal-footer">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('ag_save'))?></button>
	<a href="#" class="an-btn an-btn-primary-transparent" data-dismiss="modal"><?php e(lang('ag_cancel'))?></a>
</div>

<?php echo form_close(); ?>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('create_js', [
		'organization_members ' => $organization_members
	], true) . '</script>';
}
?>