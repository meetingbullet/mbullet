var status_lang = {
	'open' : '<?php echo lang('st_open') ?>',
	'inprogress' : '<?php echo lang('st_inprogress') ?>',
	'resolved' : '<?php echo lang('st_resolved') ?>',
	'jumped' : '<?php echo lang('st_jumped') ?>',
	'skipped' : "<?php echo lang('st_skipped') ?>",
	'parking_lot' : "<?php echo lang('st_parking_lot') ?>",
};

var homework_status_lang = {
	'open' : '<?php echo lang('hw_open') ?>',
	'done' : '<?php echo lang('hw_done') ?>',
	'undone' : '<?php echo lang('hw_undone') ?>',
};

var update_step_timer_interval,
	update_agenda_timer_intervals = [];

// Update skip votes periodly
var update_monitor_interval = setInterval(update_monitor, 3000);

// Clear all updater 
$('#step-monitor-modal').on('hide.bs.modal', function () {
	clearInterval(update_step_timer_interval);
	clearInterval(update_monitor_interval);

	$.each(update_agenda_timer_intervals, (i, item) => {
		clearInterval(item);
	});
})

// Disable all Start agenda if there is a "In Progress" agenda
if ($('.step-monitor .label-inprogress').length) {
	$('.btn-start-agenda').prop('disabled', true);
	$('.btn-finish').prop('disabled', true);
}

if ($('.step-monitor .table-agenda .label-inprogress').length == 0 && $('.step-monitor .table-agenda .label-open').length == 0) {
	$('.btn-finish').prop('disabled', false);
}

if ($('#scheduled-timer').data('actual-start-time')) {
	update_step_timer()
}

$('#datetimepicker1').datetimepicker({
	format: 'MMM DD, H:mm'
}).on('dp.change', function (ev) {
	// console.log('Client set:', ev.date.format('YYYY-MM-DD HH:mm:ss'));
	// console.log('Server set:', ev.date.utc().format('YYYY-MM-DD HH:mm:ss'));
	$('input[name="scheduled_start_time"]').val(ev.date.utc().format('YYYY-MM-DD HH:mm:ss'));
});

// Prevent duplicate binding function
$(document).off('.monitor');

$(document).on('click.monitor', '.btn-vote-skip', (e) => {
	e.preventDefault();
	var agenda_id = $(e.target).parent().parent().data('agenda-id');

	$.get('<?php e(site_url('step/vote_skip/'))?>' + agenda_id, (result) => {
		if (result == '1') {
			update_monitor();

			$(e.target).removeClass('btn-vote-skip');
			$(e.target).removeClass('an-btn-primary');
			$(e.target).addClass('an-btn-primary-transparent');
			$(e.target).prop('disabled', true);
			$(e.target).text('<?php e(lang('st_voted_skip'))?>');
		}
	});

	return false;
});

$(document).on('click.monitor', '.btn-update-step-schedule', (e) => {
	e.preventDefault();

	var time_assigned_data = "";
	var is_set_time = true;

	$('.table-agenda tr input[name="time_assigned"]').each((i, item) => {
		if ($(item).val() != '' && $(item).val() > 0) {
			time_assigned_data += "&time_assigned["+ $(item).data('agenda-id') +"]=" + $(item).val();
			$(item).removeClass('danger');
		} else {
			$(item).addClass('danger');
			is_set_time = false;
		}
	});

	if (moment($('input[name="scheduled_start_time"]').val()).isValid()) {
		$('#datetimepicker1').removeClass('danger');
	} else {
		$('#datetimepicker1').addClass('danger');
	}

	if (! is_set_time) {
		$.notify({
			message: '<?php e(lang('st_invalid_assigned_time'))?>'
		}, {
			type: 'danger',
			z_index: 1051
		});
		return false;
	}


	$.post($('.form-step-schedule').attr('action'), $('.form-step-schedule').serialize() + time_assigned_data, (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			// $('.btn-start-step').prop('disabled', false);
			$('#datetimepicker1').removeClass('danger');

			$('#step-monitor-modal').modal('hide');
			setTimeout(() => {
				location.reload();
			}, 600);
		} else {
			$('#datetimepicker1').addClass('danger');
		}
	});

	return false;
});

$(document).on('click.monitor', '.btn-start-step', (e) => {
	e.preventDefault();

	var time_assigned_data = "";
	var is_set_time = true;

	$('.table-agenda tr input[name="time_assigned"]').each((i, item) => {
		if ($(item).val() != '' && $(item).val() > 0) {
			time_assigned_data += "&time_assigned["+ $(item).data('agenda-id') +"]=" + $(item).val();
			$(item).removeClass('danger');
		} else {
			$(item).addClass('danger');
			is_set_time = false;
		}
	});

	if (! is_set_time) {
		return false;
	}

	$.post($('.form-step-schedule').attr('action'),  $('.form-step-schedule').serialize() + '&start=1' + time_assigned_data, (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$('.btn-finish').toggleClass('hidden');
			$('.btn-start-step').toggleClass('hidden');

			$('tr[data-agenda-status="open"] .btn-skip').removeClass('hidden');
			$('tr[data-agenda-status="open"] .btn-start-agenda').removeClass('hidden');
			$('tr[data-agenda-status="open"] .btn-start-agenda').prop('disabled', false);

			$('.btn-update-step-schedule').addClass('hidden');
			$('.input-group-btn-right').removeClass('input-group-btn-right');
			$('#scheduled-timer').data('actual-start-time', data.actual_start_time);
			$('#scheduled-timer').data('now', data.actual_start_time);
			update_step_timer();

			$('.table-agenda tr input[name="time_assigned"]').each((i, item) => {
				if ($(item).val() != '' && $(item).val() > 0) {
					time_assigned_data += "&time_assigned["+ $(item).data('agenda-id') +"]=" + $(item).val();
					$(item).removeClass('danger');
					$(item).addClass('hidden');
					$(item).parent().children('span').text($(item).val());
				}
			});
		}
	});

	return false;
});

$(document).on('click.monitor', '.btn-finish', (e) => {
	e.preventDefault();

	$.post($('.form-step-schedule').attr('action'),  $('.form-step-schedule').serialize() + '&finish=1', (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$('#step-monitor-modal').modal('hide');

			// Open step decider if is owner
			if ($('.step-monitor').data('is-owner') == '1') {
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
		}
	});

	return false;
});

$(document).on('keyup.monitor', '.form-td', (e) => {
	if ($(e.target).val() <= 0) {
		$(e.target).addClass('danger');
		$(e.target).parent().parent().find('.btn-start-agenda').prop('disabled', true);
	} else {
		if ($('.label-inprogress').length === 0) {
			$(e.target).parent().parent().find('.btn-start-agenda').prop('disabled', false);
			$(e.target).removeClass('danger');
		}
	}
});

$(document).on('click.monitor', '.btn-start-agenda', (e) => {
	var row = $(e.target).parent().parent();
	var time_assigned = $(row).find('input[name="time_assigned"]').val()
						? $(row).find('input[name="time_assigned"]').val()
						: $(row).find('.time-assigned').text();

	$(document).data('ajax-start-time', moment().unix());

	$.post('<?php echo site_url('step/update_agenda_status') ?>', {
		agenda_id: $(e.target).parent().parent().data('agenda-id'), 
		status: 'inprogress', 
		time_assigned: time_assigned
	}, (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$(e.target).addClass('hidden');
			$(row).find('.btn-skip').addClass('hidden');
			$(row).find('.btn-jump').removeClass('hidden');

			$(row).find('.agenda-status').data('started-on', data.started_on);
			$(row).find('.agenda-status').data('now', data.started_on);

			update_agenda_timer($(row).find('.agenda-status'));

			$('.btn-start-agenda').prop('disabled', true);

			$(row).find('input[name="time_assigned"]').addClass('hidden');
			$(row).find('.time-assigned').text($(row).find('input[name="time_assigned"]').val());
		}
	});
});

$(document).on('click.monitor', '.btn-skip', (e) => {
	var agenda_id = $(e.target).parent().parent().data('agenda-id');
	var row = $(e.target).parent().parent();
	var time_assigned = $(row).find('input[name="time_assigned"]').val()
						? $(row).find('input[name="time_assigned"]').val()
						: $(row).find('.time-assigned').text();

	$.post('<?php echo site_url('step/update_agenda_status') ?>', {
		agenda_id, 
		status: 'skipped'
	}, (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$(e.target).addClass('hidden');
			$(row).find('.btn-start-agenda').addClass('hidden');
			$(row).find('.agenda-status').html('<span class="label label-bordered label-skipped"><?php e(lang('st_skipped'))?></span>');

			if ($('.step-monitor .table-agenda .label-open, .step-monitor .table-agenda .label-inprogress').length == 0) {
				$('.btn-finish').prop('disabled', false);
			}
		}
	});
});

$(document).on('click.monitor', '.btn-jump', (e) => {
	var agenda_id = $(e.target).parent().parent().data('agenda-id');
	var row = $(e.target).parent().parent();
	var time_assigned = $(row).find('input[name="time_assigned"]').val()
						? $(row).find('input[name="time_assigned"]').val()
						: $(row).find('.time-assigned').text();

	$.post('<?php echo site_url('step/update_agenda_status') ?>', {
		agenda_id, 
		status: 'jumped'
	}, (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$(e.target).addClass('hidden');
			clearInterval(update_agenda_timer_intervals[agenda_id]);
			$(row).find('.agenda-status').html('<span class="label label-bordered label-jumped"><?php e(lang('st_jumped'))?></span>');

			$('.btn-start-agenda').each((i, item) => {
				if ( $(item).parent().parent().find('input[name="time_assigned"]').val() ) {
					$(item).prop('disabled', false);
				}
			});

			if ($('.step-monitor .table-agenda .label-open,.step-monitor .table-agenda .label-inprogress').length == 0) {
				$('.btn-finish').prop('disabled', false);
			}
		}
	});
});

$(document).on('click.monitor', '.btn-resolve', (e) => {
	e.preventDefault();
	var agenda_id = $('.form-resolve-agenda').data('agenda-id');

	$.post('<?php echo site_url('step/update_agenda_status') ?>', {
		agenda_id, 
		status: 'resolved',
		comment: $('textarea[name="comment"]').val()
	}, (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$('#resolve-agenda').modal('hide');
			$('.btn-start-agenda').prop('disabled', false);
			$('#agenda-' + agenda_id).find('.btn-jump').addClass('hidden');
			$('#agenda-' + agenda_id).find('.agenda-status').html('<span class="label label-bordered label-resolved"><?php e(lang('st_resolved'))?></span>');

			if ($('.step-monitor .table-agenda .label-open,.step-monitor .table-agenda .label-inprogress').length == 0) {
				$('.btn-finish').prop('disabled', false);
			}
		}
	});
});

$(document).on('click.monitor', '.btn-parking-lot', (e) => {
	e.preventDefault();
	var agenda_id = $('.form-resolve-agenda').data('agenda-id');

	$.post('<?php echo site_url('step/update_agenda_status') ?>', {
		agenda_id, 
		status: 'parking_lot',
		comment: $('textarea[name="comment"]').val()
	}, (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$('#resolve-agenda').modal('hide');
			$('.btn-start-agenda').prop('disabled', false);
			$('#agenda-' + agenda_id).find('.btn-jump').addClass('hidden');
			$('#agenda-' + agenda_id).find('.agenda-status').html('<span class="label label-bordered label-parking_lot"><?php e(lang('st_parking_lot'))?></span>');

			if ($('.step-monitor .table-agenda .label-open,.step-monitor .table-agenda .label-inprogress').length == 0) {
				$('.btn-finish').prop('disabled', false);
			}
		}
	});
});

// Editable homework when status is OPEN
if ($('.step-monitor[data-status="open"]').length > 0
	|| $('.step-monitor[data-status="ready"]').length > 0
	|| $('.step-monitor[data-status="inprogress"]').length > 0
	) {
	homework_editable();
}

// Make this function reuse-able to apply after dynamic creating new Homework
function homework_editable() {
	$('tr.homework.can-edit .description').editable({
		// Disable display method for word_limiter functionality in success response
		display: function(value, response) {
			return false;
		},

		success: function(data) {
			data = JSON.parse(data);

			if (data.message_type == 'danger') {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				return;
			}

			$(this).data('value', data.value);
			$(this).html(data.value);
		}
	});

	$('tr.homework.can-edit .time-spent').editable({
		success: function(data, newValue) {
			return {newValue: parseFloat(newValue)};
		}
	});
}

$(document).on('click.monitor', 'tr.homework.can-edit .btn-update-homework-status', (e) => {
	$.post("<?php echo site_url('homework/ajax_edit') ?>", {
		pk: $(e.target).data('pk'),
		name: 'status',
		value: $(e.target).data('value'),
	}, (data) => {
		data = JSON.parse(data);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			var btn_status = $(e.target).parents('.btn-group').children('.btn-status');
			var btn_status_caret = $(e.target).parents('.btn-group').children('.btn.dropdown-toggle');

			$(btn_status).text( $(e.target).text() );
			$(btn_status).prop('class', 'btn btn-status label-' + $(e.target).data('value'));
			$(btn_status).data('status', $(e.target).data('value'));
			$(btn_status_caret).prop('class', 'btn dropdown-toggle label-' + $(e.target).data('value'));

			$(e.target).parents('ul').find('.btn-update-homework-status').removeClass('hidden');
			$(e.target).addClass('hidden');
		}
	}).fail((data) => {
		data = JSON.parse(data.responseText);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});
		console.log(data);
	});
});

$(document).on('click.monitor', '.time-assigned', (e) => {
	if ($(e.target).parent().parent().find('.agenda-status span').text() == 'Open') {
		$(e.target).parent().find('input').removeClass('hidden');
		$(e.target).hide();
	}
});

$('.table-agenda .agenda-status').each((index, item) => {
	if ( $(item).data('started-on') ) {
		update_agenda_timer(item);
	}
});


function update_step_timer(clock)
{
	var clock = '#scheduled-timer';

	var eventTime = moment($(clock).data('actual-start-time'), 'YYYY-MM-DD HH:mm:ss').unix(),
		ajax_start_time = $(document).data('ajax-start-time'),
		request_time = ajax_start_time ? ((moment().unix() - ajax_start_time) / 2) : 0,
		currentTime = moment($(clock).data('now'), 'YYYY-MM-DD HH:mm:ss').unix() + request_time,
		diffTime = currentTime - eventTime,
		duration = moment.duration(diffTime * 1000, 'milliseconds'),
		interval = 1000;

	// console.log('currentTime - eventTime', currentTime - eventTime);
	// console.log(' Math.round(request_time)', Math.round(request_time));
		
	$(clock).removeClass('hidden');

	update_step_timer_interval = setInterval(function(){

		duration = moment.duration(duration.asMilliseconds() + interval, 'milliseconds');
		var d = moment.duration(duration).days(),
			h = moment.duration(duration).hours(),
			m = moment.duration(duration).minutes(),
			s = moment.duration(duration).seconds();

		if (d > 0) {
			h = parseInt(h) + d * 24;
		}

		h = h <= 9 ? '0' + h : h;
		m = m <= 9 ? '0' + m : m;
		s = s <= 9 ? '0' + s : s;

		$(clock).html(h + ':' + m + ':' + s);

	}, interval);
}

function update_agenda_timer(clock)
{
	var agenda_id = $(clock).parent().data('agenda-id'),
		time_assigned = $(clock).data('time-assigned'),
		ajax_start_time = $(document).data('ajax-start-time'),
		request_time = ajax_start_time ? ((moment().unix() - ajax_start_time) / 2) : 0;
		eventTime = moment($(clock).data('started-on'), 'YYYY-MM-DD HH:mm:ss').unix(),
		currentTime = moment($(clock).data('now'), 'YYYY-MM-DD HH:mm:ss').unix() + request_time,
		diffTime = currentTime - eventTime,
		duration = moment.duration(diffTime * 1000, 'milliseconds'),
		interval = 1000;
	
	// console.log('Request time diff:', request_time);
	// console.log('Time diff:', diffTime);

	// Show $(clock)
	$(clock).html('<span class="label label-warning label-inprogress label-bordered"><?php e(lang('st_in_progress'))?></span> ');

	var $time = $('<span class="time" ></span>').appendTo($(clock));

		update_agenda_timer_intervals[agenda_id] = setInterval(function(){

			duration = moment.duration(duration.asMilliseconds() + interval, 'milliseconds');
			var d = moment.duration(duration).days(),
				h = moment.duration(duration).hours(),
				m = moment.duration(duration).minutes(),
				s = moment.duration(duration).seconds();

			// Time alotted for agenda
			if (duration.asMinutes() >= time_assigned && $('.step-monitor').data('is-owner') == '1') {
				if (update_agenda_timer_intervals[agenda_id]) {
					clearInterval(update_agenda_timer_intervals[agenda_id]);

					$.getJSON('<?php echo site_url('step/resolve_agenda/') ?>' + agenda_id, (data) => {
						if (data.message != '') {
							$.notify({
								message: data.message
							}, {
								type: data.message_type,
								z_index: 1051
							});
						}

						$('#resolve-agenda .modal-content').html(data.modal_content);
						$('#resolve-agenda').modal({backdrop: 'static'});
					});
				}
			}

			if (d > 0) {
				h = parseInt(h) + d * 24;
			}

			h = h <= 9 ? '0' + h : h;
			m = m <= 9 ? '0' + m : m;
			s = s <= 9 ? '0' + s : s;

			$time.text(h + ':' + m + ':' + s);

		}, interval);
}

function update_monitor()
{
	$(document).data('ajax-start-time', moment().unix());

	$.get('<?php e(site_url('step/get_monitor_data/'))?>' + $('.step-monitor').data('step-id'), (result) => {
		data = JSON.parse(result);

		if (data.message_type == 'danger') {
			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});

			return;
		}

		if ( ! (data.step.status == 'open' || data.step.status == 'ready' || data.step.status == 'inprogress') ) {
			$.notify({
				message: '<?php e(lang('st_step_finished'))?>'
			}, {
				type: 'success',
				z_index: 1051
			});
			$('#step-monitor-modal').modal('hide');

			// Open step decider if is owner
			if ($('.step-monitor').data('is-owner') == '1') {
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
			} else {
				// Wait for owner finish decider
				swal({
					title: '<?php echo lang('st_waiting') ?>',
					text: '<?php echo lang('st_waiting_evaluator') ?>',
					allowEscapeKey: false,
					imageUrl: '<?php echo Template::theme_url('images/clock.svg') ?>',
					showConfirmButton: false
				});

				var check_state_interval = setInterval(function(){
					$.get('<?php echo site_url('step/check_state/' . $step_key) ?>').done(function(data) {
						if (data == 1) {
							clearInterval(check_state_interval);
							swal.close();

							$.get('<?php echo site_url('step/evaluator/' . $step_key) ?>').done(function(data) {
								data = JSON.parse(data);
								$('#step-monitor-modal-evaluator .modal-content').html(data.modal_content);
								$('#step-monitor-modal-evaluator').modal({
									backdrop: 'static'
								});
							});
						}
					});
				}, 3000);
			}
		}

		// Real-time step joiner
		$('#step-joiner span').addClass('inactive');
		$.each(data.online_members, (index, item) => {
			if ($('#step-joiner span[data-user-id="'+ item.user_id +'"]').length) {
				$('#step-joiner span[data-user-id="'+ item.user_id +'"]').removeClass('inactive');
			} else {
				var new_joiner_html = '<span class="avatar" data-user-id="'+ item.user_id +'" style="background-image: url(\''+ item.avatar +'\'); display: none;"></span>';
				$(new_joiner_html).appendTo('#step-joiner').slideDown(300);
			}
		});

		// Remove joiner whose has left
		$('#step-joiner span.inactive').animate({width: 0, marginRight: 0}, 300, function() {
			$(this).remove();
		});


		// Real-time agenda data
		$.each(data.agendas, (index, item) => {
			old_vote = parseInt($('#agenda-' + item.agenda_id + ' .skip-votes').text());
			old_status = $('#agenda-' + item.agenda_id).data('agenda-status');

			if (item.skip_votes != old_vote) {
				$('#agenda-' + item.agenda_id + ' .skip-votes').text(item.skip_votes);
				$('#agenda-' + item.agenda_id + ' .skip-votes').effect("highlight", {}, 3000);
			}

			if (item.status != old_status && $('.step-monitor').data('is-owner') == 0) {
				$('#agenda-' + item.agenda_id).data('agenda-status', item.status);
				$('#agenda-' + item.agenda_id + ' .agenda-status').effect("highlight", {}, 3000);
				$('#agenda-' + item.agenda_id + ' .label').removeClass('label-' + old_status);
				$('#agenda-' + item.agenda_id + ' .label').addClass('label-' + item.status);

				if (item.status == 'inprogress') {
					$('#agenda-' + item.agenda_id + ' .label').data('started-on', item.started_on);
					$('#agenda-' + item.agenda_id + ' .label').data('now', item.current_time);

					update_agenda_timer($('#agenda-' + item.agenda_id + ' .agenda-status'));
				} else {
					if (update_agenda_timer_intervals[item.agenda_id]) {
						clearInterval(update_agenda_timer_intervals[item.agenda_id]);
						$('#agenda-' + item.agenda_id + ' .agenda-status > span').text('');
					}

					if (item.status == 'jumped' || item.status == 'resolved' || item.status == 'skipped' || item.status == 'parking_lot') {
						$('#agenda-' + item.agenda_id + ' .btn-vote-skip').addClass('hidden');
					}
				}

				$('#agenda-' + item.agenda_id + ' .label').attr('class', 'label label-bordered label-' + item.status);
				$('#agenda-' + item.agenda_id + ' .label').text(status_lang[item.status]);
			}
		});

		// Real-time Homework data
		$.each(data.homeworks, (index, item) => {
			old_description = $('#homework-' + item.homework_id + ' .description').data('value');
			old_status = $('#homework-' + item.homework_id + ' .btn-status').data('status');
			old_time_spent = parseFloat( $('#homework-' + item.homework_id + ' .time-spent').text() );

			if (item.description != old_description) {
				$('#homework-' + item.homework_id + ' .description').editable('setValue', item.description);
				$('#homework-' + item.homework_id + ' .description').removeClass('text-muted');
				$('#homework-' + item.homework_id + ' .description').html(item.short_description);
				$('#homework-' + item.homework_id + ' .description').data('value', item.description);
				$('#homework-' + item.homework_id + ' .description-container').effect("highlight", {}, 3000);
			}

			if (item.status != old_status) {
				$('#homework-' + item.homework_id + ' .btn-status').text(homework_status_lang[item.status]);
				$('#homework-' + item.homework_id + ' .btn-status').data('status', item.status);
				$('#homework-' + item.homework_id + ' .btn-status').prop('class', 'btn btn-status label-' + item.status);
				$('#homework-' + item.homework_id + ' .btn-status + .btn').prop('class', 'btn dropdown-toggle label-' + item.status);
				$('#homework-' + item.homework_id + ' .status-container').effect("highlight", {}, 3000);
			}

			if (item.time_spent != old_time_spent) {
				$('#homework-' + item.homework_id + ' .time-spent').editable('setValue', item.time_spent);
				$('#homework-' + item.homework_id + ' .time-spent').text(item.time_spent);
				$('#homework-' + item.homework_id + ' .time-spent-container').effect("highlight", {}, 3000);
			}
		});
	});
}