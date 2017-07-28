$(document).on('click', '.mb-btn-delete-role', function(e) {
	e.preventDefault();
	var check = null;
	var _this = this;
	$.get("<?php echo site_url('admin/roles/check/') ?>" + $(_this).data('role-id'), (data) => {
				check = JSON.parse(data).m;
			
	if (check=="false"){
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
			$.get("<?php echo site_url('admin/roles/delete/') ?>" + $(_this).data('role-id'), (data) => {
				data = JSON.parse(data);
				$.mbNotify(data.message, data.message_type);

				if (data.message_type == 'success') {
					$('#role-' + $(_this).data('role-id')).slideUp();
					$('#update-role-modal').modal('hide');
				}

				swal.close();
			});
		});
		if ($(_this).parent().parent().children(".list-join-default").html().trim().length != 0 ) {
			swal({
				title: "<?php e(lang('rl_error')) ?>",
				text: "<?php e(lang('rl_cannot_delete_default_role')) ?>",
				type: "warning",
			});
		}
	} else {
		swal({
			title: "<?php e(lang('rl_are_you_sure')) ?>",
			text: "<?php e(lang('rl_role_having_user')) ?>",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo lang('rl_ok') ?>",
			cancelButtonText: "<?php e(lang('rl_cancel')) ?>",
			closeOnConfirm: false,
			closeOnCancel: false
		},
		function(isConfirm){
		if (isConfirm) {
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
			$.get("<?php echo site_url('admin/roles/delete/') ?>" + $(_this).data('role-id'), (data) => {
				data = JSON.parse(data);
				$.mbNotify(data.message, data.message_type);

				if (data.message_type == 'success') {
					$('#role-' + $(_this).data('role-id')).slideUp();
					$('#update-role-modal').modal('hide');
				}

				swal.close();
			});
		});
		} else {
			swal("Cancelled", "", "error");
		}
		});
	}
});
});