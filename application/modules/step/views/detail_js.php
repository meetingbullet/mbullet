// Edit step
$('#edit-step').click((e) => {
	e.preventDefault();

	$.get('<?php e(site_url('step/edit/' . $step_key)) ?>', (data) => {
		data = JSON.parse(data);
		$('.modal-edit .modal-content').html(data.modal_content);
		$('.modal-edit').modal({backdrop: "static"});
	});

});

// Edit step
$('#open-step-monitor').click((e) => {
	e.preventDefault();
	$('.modal-monitor .modal-content').html('');

	$.get('<?php e(site_url('step/monitor/' . $step_key)) ?>', (data) => {
		data = JSON.parse(data);
		$('.modal-monitor .modal-content').html(data.modal_content);
		$('.modal-monitor').modal();
		$('.modal-monitor').modal({backdrop: "static"});
	});
});

$('#start-step').click((e) => {
	e.preventDefault();
	var _this = this;

	$.post('<?php e(site_url('step/update_status/' . $step_key)) ?>', {status: 'ready'}, (result) => {
		data = JSON.parse(result);
		
		if (data.message_type == 'success') {
			$('#start-step').addClass('hidden');
			$('#open-step-monitor').removeClass('hidden');
		}
	});
});

// Set form-ajax to work inside a modal
$(document).on("submit", '.form-ajax', (e) => {
	e.preventDefault();

	var method = $(e.target).attr('method') ? $(e.target).attr('method') : 'post';
	var data = $(e.target).serialize();

	// Since serialize does not include form's action button, 
	// we need to add it on our own.
	data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

	// Clear script in an opened modal for Javascript run after modal is updated
	$('.modal.in .modal-content script').text('');

	$.ajax({
		type: "POST",
		url: $(e.target).attr('action'),
		data: data,
		success: (data) => {
			data = JSON.parse(data);

			if (data.close_modal === 0) {
				if ($('.modal.in').length) {
					$('.modal.in .modal-content').html(data.modal_content);
				} else {

					if (data.id) {
						$(data.id + ' .modal-content').html(data.modal_content);
						$(data.id).modal('show');
					} else {
						$('.modal .modal-content').html(data.modal_content);
						$('.modal').modal('show');
					}
				}
			} else {
				$('.modal.in .modal-content').html('');
				$('.modal.in').modal('hide');
			}

			if (data.message_type) {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				if (data.message_type == 'success') {
					// @TODO Refresh Step list
					location.reload();
				}
			}
		}
	});
});