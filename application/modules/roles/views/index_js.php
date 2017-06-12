$(document).on('click', '.mb-btn-delete-role', function(e) {
	e.preventDefault();
	var _this = this;
	swal({
		title: "<?php e(lang('rl_are_you_sure')) ?>",
		text: "<?php e(lang('rl_you_wont_be_able_to_recover_role')) ?>",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "<?php echo lang('rl_yes_delete_role') ?>",
		closeOnConfirm: false
	},
	function(){
		$.get("<?php echo site_url('roles/delete/') ?>" + $(_this).data('role-id'), (data) => {
			data = JSON.parse(data);
			$.mbNotify(data.message, data.message_type);

			if (data.message_type == 'success') {
				$('#role-' + $(_this).data('role-id')).slideUp();
				$('#update-role-modal').modal('hide');
			}

			swal.close();
		});
	});
});