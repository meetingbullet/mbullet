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

			/* 
				If one of the agendas is marked as Closed Parking Lot the step owner is redirected to 
				the Step creation screen and prompted to create a new step to resolve the Closed Parking Lot agenda.
			*/
			if ($('.confirmation-status option[value="open_parking_lot"]:selected').length > 0) {
				$.post('<?php e(site_url('step/create/' . $action_key)) ?>', {from_step: '<?php e($step_id) ?>'}, (data) => {
					data = JSON.parse(data);
					$('#create-step .modal-content').html(data.modal_content);
					$('#create-step').modal({backdrop: "static"});

					// Open Evaluator for Owner
					$('#create-step').on('hidden.bs.modal', function () {
						// @Bao: Open Evaluator for Owner
						$.get('<?php echo site_url('step/evaluator/' . $step_key) ?>').done(function(data) {
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
				$.get('<?php echo site_url('step/evaluator/' . $step_key) ?>').done(function(data) {
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