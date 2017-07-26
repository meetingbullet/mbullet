<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title"><?php echo lang('ad_tm_edit_user') . ': ' . $user->full_name ?></h4>
</div> <!-- end MODAL-HEADER -->

<?php echo form_open($this->uri->uri_string(), ['class' => 'form-ajax edit-user-form']) ?>

<div class='container-fluid modal-body'>
	<?php echo mb_form_input('text', 'title', lang('ad_tm_title'), false, set_value('title', isset($user->title) ? $user->title : ''), $class = 'an-form-control', '', lang('ad_tm_title')) ?>
	<?php echo mb_form_dropdown('cost_of_time', [
		'1' => 'XS',
		'2' => 'S',
		'3' => 'M',
		'4' => 'L',
		'5' => 'XL'
	], set_value('cost_of_time', isset($user->cost_of_time) ? $user->cost_of_time : null), lang('ad_tm_cost_of_time'), 'class="an-form-control"', '', true) ?>
	<?php 
		if ($disable) {
			echo mb_form_dropdown('role_id', $roles, set_value('role', isset($user->role_id) ? $user->role_id : null), lang('ad_tm_role'), 'class="an-form-control" disabled="disabled"' , '', true);
		} else {
			$temp = [];
			foreach ($permissions as $permission) {
				$temp[$permission->manage_role_id] = $permission->name;
			}
			$roles = $temp;
			echo mb_form_dropdown('role_id', $roles, set_value('role', isset($user->role_id) ? $user->role_id : null), lang('ad_tm_role'), 'class="an-form-control" ' , '', true);
		}
	?>
	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label class="pull-right"><?php echo lang('ad_tm_enabled') ?><span class="required">*</span></label>
		</div>
		<div class="col-md-9 col-sm-12">
			<div class="an-switch-box-wrapper success">
				<div class="lcs_wrap" id="switch">
					<input name="enabled" value="1" type="checkbox" <?php echo empty(set_value('enabled', isset($user->enabled) ? $user->enabled : '')) ? '' : 'checked'?>>
					<div class="lcs_switch lcs_checkbox_switch <?php echo empty(set_value('enabled', isset($user->enabled) ? $user->enabled : '')) ? 'lcs_off' : 'lcs_on'?>">
						<div class="lcs_cursor"></div>
						<div class="lcs_label lcs_label_on">ON</div>
						<div class="lcs_label lcs_label_off">OFF</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('ad_tm_update'))?></button>
	<a href="#" class="an-btn an-btn-danger-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('ad_tm_cancel'))?></a>
</div>

<?php echo form_close(); ?>
<script type="text/javascript">
$('#switch').click(function() {
	var that = $(this);
	that.find('.lcs_switch.lcs_checkbox_switch').toggleClass('lcs_off').toggleClass('lcs_on');

	if (that.find('.lcs_switch.lcs_checkbox_switch').hasClass('lcs_off')) {
		that.find('input[name="enabled"]').removeAttr('checked');
	} else {
		that.find('input[name="enabled"]').attr('checked', '');
	}
});
</script>