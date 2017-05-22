// Read more Notes & Goal
var rm_option = {
	speed: 300,
	moreLink: '<a class=\'readmore rm-more\' href="#"><?php e(lang('show_more'))?></a>',
	lessLink: '<a class=\'readmore rm-less\' href="#"><?php e(lang('show_less'))?></a>'
};

$('.goal, .step-notes').readmore(rm_option);

// Edit step
$('#edit-step').click((e) => {
	e.preventDefault();

	$.get('<?php e(site_url('step/edit/' . $step_key)) ?>', (data) => {
		data = JSON.parse(data);
		$('.modal-edit .modal-content').html(data.modal_content);
		$('.modal-edit').modal({backdrop: "static"});
	});

});

// Open step monitor
$('#open-step-monitor').click((e) => {
	e.preventDefault();

	// Adjust diff between server and client on counters
	$(document).data('ajax-start-time', moment().unix());
	
	$('.modal-monitor .modal-content').html('');

	$.get('<?php e(site_url('step/monitor/' . $step_key)) ?>', (data) => {
		data = JSON.parse(data);

		if (data.modal_content == '') {
			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});
			return;
		}

		$('.modal-monitor .modal-content').html(data.modal_content);
		$('.modal-monitor').modal({backdrop: "static"});
	});

	if ($(this).hasClass('step-open')) {
		$(this).removeClass('step-open');
		$(this).find('span').text('<?php echo lang('st_monitor')?>')
	}
});

// Open step decider
$('#open-step-decider').click((e) => {
	e.preventDefault();

	$.get('<?php e(site_url('step/decider/' . $step_key)) ?>', (data) => {
		data = JSON.parse(data);

		if (data.modal_content == '') {
			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});
			return;
		}

		$('#step-decider .modal-content').html(data.modal_content);
		$('#step-decider').modal({backdrop: "static"});
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

// Open step decider if there is a task without confirmed status
if ($('#step-status').data('is-owner') == '1' && ($('#step-status').data('status') == 'resolved' || $('#step-status').data('status') == 'finished')) {
	$('.table-detail-task tr').each((i, item) => {
		if ($(item).data('confirm-status') == '') {
			$('#step-decider .modal-content').html('');

			$.get('<?php e(site_url('step/decider/' . $step_key)) ?>', (data) => {
				data = JSON.parse(data);

				if (data.modal_content == '') {
					$.notify({
						message: data.message
					}, {
						type: data.message_type,
						z_index: 1051
					});
					return;
				}

				$('#step-decider .modal-content').html(data.modal_content);
				$('#step-decider').modal({backdrop: "static"});
			});
		}
	});
}

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

			if (data.message_type) {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				if (data.message_type == 'success') {

					// Task created
					if ($(e.target).prop('id') == 'create-task') {
						$('#task-list tbody').append($.templates('#task-row').render(data.data));
						$('#task-list tbody tr:last-child').effect("highlight", {}, 3000);
					}

					// Step updated
					if ($(e.target).prop('id') == 'form-update-step') {
						// Gather form data
						var step_name = $('#form-update-step input[name="name"]').val();
						var lang_status = $('#form-update-step select[name="status"] option:selected').text();
						var status = $('#form-update-step select[name="status"] option:selected').val();
						var goal = $('#form-update-step textarea[name="goal"]').val();
						var owner_id = $('#form-update-step input[name="owner_id"]').val();
						var team = $('#form-update-step input[name="team"]').val().split(',');
						var owner_html = $('.step-detail .owner').html();
						var resource_html = '';

						project_members.forEach((item) => {
							if (item.id == owner_id) {
								owner_html = '<img class="user-avatar" title="'+ item.name +'" src="'+ item.avatar +'" style="width: 24px; height: 24px"> <span class="user-name">'+ item.name +'</span>';
							}

							if (team.indexOf(item.id) >= 0) {
								resource_html += '<li>\
													<img class="user-avatar" title="'+ item.name + '" src="'+ item.avatar + '" style="width: 24px; height: 24px">\
													<span class="user-name">'+ item.name + '</span>\
													<span class="badge badge-'+ item.cost_of_time + ' badge-bordered pull-right">M</span>\
												</li>';
							}
						});

						// Update view
						$('#step-name').text(step_name);
						$('.step-detail .status').html('<span class="label label-'+ status +' label-bordered" id="step-status" data-status="'+ status +'" data-is-owner="'+ $('#step-status').data('is-owner') +'">'+ lang_status +'</span>');
						$('.step-detail .owner').html(owner_html);
						$('.step-detail .goal').html(goal);
						$('.goal').readmore(rm_option);
						$('#step-resource').html(resource_html);
					} 
				}
			}
			
			if (data.close_modal == 0) {
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
		}
	});
});

// open step evaluator
$('#open-step-evaluator').click((e) => {
	e.preventDefault();
	var is_owner = $('#open-step-evaluator').data('is-owner');
	if (is_owner == 0) {
		swal({
			title: '<?php echo lang('st_waiting') ?>',
			text: '<?php echo lang('st_waiting_evaluator') ?>',
			allowEscapeKey: false,
			imageUrl: '<?php echo Template::theme_url('images/clock.svg') ?>',
			showConfirmButton: false
		});

		var interval = setInterval(function(){
			$.get('<?php echo site_url('step/check_state/' . $step_key) ?>').done(function(data) {
				if (data == 1) {
					clearInterval(interval);
					swal.close();

					$.get('<?php echo site_url('step/evaluator/' . $step_key) ?>').done(function(data) {
						data = JSON.parse(data);
						$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
						$('.modal-monitor-evaluator').modal({
							backdrop: 'static'
						});
					});
				}
			});
		}, 3000);
	} else {
		$.get('<?php echo site_url('step/evaluator/' . $step_key) ?>').done(function(data) {
			data = JSON.parse(data);
			$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
			$('.modal-monitor-evaluator').modal({
				backdrop: 'static'
			});
		});
	}
});

