// Enable jQuery tooltip
$('[data-toggle="tooltip"]').tooltip(); 

// Prevent duplicate binding function
$(document).off('.decider');

$(document).on('submit.decider', '.form-meeting-decider', (e) => {
	// Validation
	var is_valid = true;
	$('.form-meeting-decider .confirmation-status').each((i, item) => {
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
			$('#meeting-decider-modal').modal('hide');

			/* 
				If one of the agendas is marked as Open Parking Lot the meeting owner is redirected to 
				the Meeting creation screen and prompted to create a new meeting to resolve the Closed Parking Lot agenda.
			*/
			if ($('.confirmation-status option[value="open_parking_lot"]:selected').length > 0) {
				$.post('<?php e(site_url('meeting/create/' . $project_key)) ?>', {from_meeting: '<?php e($meeting_id) ?>'}, (data) => {
					data = JSON.parse(data);
					$('#create-meeting .modal-content').html(data.modal_content);
					$('#create-meeting').modal({backdrop: "static"});

					// Open Evaluator for Owner
					$('#create-meeting').on('hidden.bs.modal', function () {
						// @Bao: Open Evaluator for Owner
						$.get('<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>').done(function(data) {
							data = JSON.parse(data);
							$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
							$('.modal-monitor-evaluator').modal({
								backdrop: 'static'
							});
						});
					});
				});
			} else {
				// @Bao: Open Evaluator for Owner
				$.get('<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>').done(function(data) {
					data = JSON.parse(data);
					$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
					$('.modal-monitor-evaluator').modal({
						backdrop: 'static'
					});
				});
			}
		}
	})

	return false;
});