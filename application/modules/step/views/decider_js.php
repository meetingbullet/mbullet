// Prevent duplicate binding function
$(document).off('.decider');

$(document).on('submit.decider', '.form-step-decider', (e) => {
	// Validation
	var is_valid = true;
	$('.confirmation-status').each((i, item) => {
		if ($(item).val() === null) {
			$(item).addClass('danger');
			is_valid = false;
		} else {
			$(item).removeClass('danger');
		}
	});

	if ( ! is_valid) {
		$.notify({
			message: '<?php e(lang('st_please_select_all_confirmation_status'))?>'
		}, {
			type: 'danger',
			z_index: 1051
		});
		return false;
	}

	$.post($(e.target).attr('action'), $(e.target).serialize(), (result) => {
		var data = JSON.parse(result);

		if (data.message_type) {
			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});
		}

		if (data.message_type == 'success') {
			$('#step-decider').modal('hide');
			setTimeout(() => {
				location.reload() 
			}, 600);
		}
	})

	return false;
});