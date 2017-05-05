<?php
$task_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
];
?>

var update_step_timer_interval,
	update_status_timer_intervals = [];

// Update skip votes periodly
var update_skip_votes_interval = -1;

// Clear all updater 
$('.modal-monitor').on('hidden.bs.modal', function () {
	clearInterval(update_step_timer_interval);
	clearInterval(update_skip_votes_interval);

	$.each(update_status_timer_intervals, (i, item) => {
		clearInterval(item);
	});
})

// Disable all Start task if there is a "In Progress" Task
if ($('.label-inprogress').length) {
	$('.btn-start-task').prop('disabled', true);
	$('.btn-finish').prop('disabled', true);
	update_skip_votes_interval = setInterval(update_skip_votes, 3500);
}

if ($('.label-inprogress').length == 0 && $('.label-open').length == 0) {
	$('.btn-finish').prop('disabled', false);
}

if ($('#scheduled-timer').data('actual-start-time')) {
	update_step_timer()
}

$('input[name="scheduled_time"]').daterangepicker({
	timePicker: true,
	timePicker24Hour: true,
	opens: 'left',
	autoUpdateInput: false,
	locale: {
		format: 'MMM DD, H:mm'
	}
}, (start, end) => {
	$('#scheduled_start_time').val(start.format('YYYY-MM-DD HH:mm:ss'));
	$('#scheduled_end_time').val(end.format('YYYY-MM-DD HH:mm:ss'));
	$('input[name="scheduled_time"]').val(start.format('MMM DD, H:mm') + ' - ' + end.format('MMM DD, H:mm'));
});

// Prevent duplicate binding function
$(document).off('.monitor');

$(document).on('click.monitor', '.btn-vote-skip', (e) => {
	e.preventDefault();
	var task_id = $(e.target).parent().parent().data('task-id');

	$.get('<?php e(site_url('step/vote_skip/'))?>' + task_id, (result) => {
		if (result == '1') {
			update_skip_votes();

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

	$.post($('.form-step-schedule').attr('action'), $('.form-step-schedule').serialize(), (result) => {
		data = JSON.parse(result);

		$.notify({
			message: data.message
		}, {
			type: data.message_type,
			z_index: 1051
		});

		if (data.message_type == 'success') {
			$('.btn-start-step').prop('disabled', false);
		}
	});

	return false;
});

$(document).on('click.monitor', '.btn-start-step', (e) => {
	e.preventDefault();

	$.post($('.form-step-schedule').attr('action'),  $('.form-step-schedule').serialize() + '&start=1', (result) => {
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

			$('tr[data-task-status="open"] .btn-skip').removeClass('hidden');
			$('tr[data-task-status="open"] .btn-start-task').removeClass('hidden');

			$('.btn-update-step-schedule').addClass('hidden');
			$('.input-group-btn-right').removeClass('input-group-btn-right');
			$('#scheduled-timer').data('actual-start-time', data.actual_start_time);
			$('#scheduled-timer').data('now', data.actual_start_time);
			update_step_timer();
		}
	});

	return false;
});

$(document).on('keyup.monitor', '.form-td', (e) => {
	if ($(e.target).val() <= 0) {
		$(e.target).addClass('danger');
		$(e.target).parent().parent().find('.btn-start-task').prop('disabled', true);
	} else {
		if ($('.label-inprogress').length === 0) {
			$(e.target).parent().parent().find('.btn-start-task').prop('disabled', false);
			$(e.target).removeClass('danger');
		}
	}
});

$(document).on('click.monitor', '.btn-start-task', (e) => {
	var row = $(e.target).parent().parent();
	var time_assigned = $(row).find('input[name="time_assigned"]').val()
						? $(row).find('input[name="time_assigned"]').val()
						: $(row).find('.time-assigned').text();

	$.post('<?php echo site_url('step/update_task_status') ?>', {
		task_id: $(e.target).parent().parent().data('task-id'), 
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

			$(row).find('.task-status').data('started-on', data.started_on);
			$(row).find('.task-status').data('now', data.started_on);

			update_status_timer($(row).find('.task-status'));

			$('.btn-start-task').prop('disabled', true);

			$(row).find('input[name="time_assigned"]').addClass('hidden');
			$(row).find('.time-assigned').text($(row).find('input[name="time_assigned"]').val());
		}
	});
});

$(document).on('click.monitor', '.btn-skip', (e) => {
	var task_id = $(e.target).parent().parent().data('task-id');
	var row = $(e.target).parent().parent();
	var time_assigned = $(row).find('input[name="time_assigned"]').val()
						? $(row).find('input[name="time_assigned"]').val()
						: $(row).find('.time-assigned').text();

	$.post('<?php echo site_url('step/update_task_status') ?>', {
		task_id, 
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
			$(row).find('.btn-start-task').addClass('hidden');
			$(row).find('.task-status').html('<span class="<?php e($task_status_labels['skipped'])?>"><?php e(lang('st_skipped'))?></span>');
		}
	});
});

$(document).on('click.monitor', '.btn-jump', (e) => {
	var task_id = $(e.target).parent().parent().data('task-id');
	var row = $(e.target).parent().parent();
	var time_assigned = $(row).find('input[name="time_assigned"]').val()
						? $(row).find('input[name="time_assigned"]').val()
						: $(row).find('.time-assigned').text();

	$.post('<?php echo site_url('step/update_task_status') ?>', {
		task_id, 
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
			clearInterval(update_status_timer_intervals[task_id]);
			$(row).find('.task-status').html('<span class="<?php e($task_status_labels['jumped'])?>"><?php e(lang('st_jumped'))?></span>');

			$('.btn-start-task').each((i, item) => {
				if ( $(item).parent().parent().find('input[name="time_assigned"]').val() ) {
					$(item).prop('disabled', false);
				}
			});
		}
	});
});

$(document).on('click.monitor', '.btn-resolve', (e) => {
	e.preventDefault();
	var task_id = $('.form-resolve-task').data('task-id');

	$.post('<?php echo site_url('step/update_task_status') ?>', {
		task_id, 
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
			$('#resolve-task').modal('hide');
			$('.btn-start-task').prop('disabled', false);
			$('#task-' + task_id).find('.btn-jump').addClass('hidden');
			$('#task-' + task_id).find('.task-status').html('<span class="<?php e($task_status_labels['resolved'])?>"><?php e(lang('st_resolved'))?></span>');
		}
	});
});

$(document).on('click.monitor', '.btn-parking-lot', (e) => {
	e.preventDefault();
	var task_id = $('.form-resolve-task').data('task-id');

	$.post('<?php echo site_url('step/update_task_status') ?>', {
		task_id, 
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
			$('#resolve-task').modal('hide');
			$('.btn-start-task').prop('disabled', false);
			$('#task-' + task_id).find('.btn-jump').addClass('hidden');
			$('#task-' + task_id).find('.task-status').html('<span class="<?php e($task_status_labels['parking_lot'])?>"><?php e(lang('st_parking_lot'))?></span>');
		}
	});
});

$(document).on('click.monitor', '.time-assigned', (e) => {
	if ($(e.target).parent().parent().find('.task-status span').text() == 'Open') {
		$(e.target).parent().find('input').removeClass('hidden');
		$(e.target).hide();
	}
});

$('.task-status').each((index, item) => {
	if ( $(item).data('started-on') ) {
		update_status_timer(item);
	}
});


function update_step_timer(clock)
{
	var clock = '#scheduled-timer';

	var eventTime = moment($(clock).data('actual-start-time'), 'YYYY-MM-DD HH:mm:ss').unix(),
		currentTime = moment($(clock).data('now'), 'YYYY-MM-DD HH:mm:ss').unix(),
		diffTime = currentTime - eventTime,
		duration = moment.duration(diffTime * 1000, 'milliseconds'),
		interval = 1000;
		
	$(clock).removeClass('hidden');

	update_step_timer_interval = setInterval(function(){

		duration = moment.duration(duration.asMilliseconds() + interval, 'milliseconds');
		var d = moment.duration(duration).days(),
			h = moment.duration(duration).hours(),
			m = moment.duration(duration).minutes(),
			s = moment.duration(duration).seconds();

		h = h < 9 ? '0' + h : h;
		m = m < 9 ? '0' + m : m;
		s = s < 9 ? '0' + s : s;

		if (d > 0) {
			h += d * 24;
		}

		$(clock).html(h + ':' + m + ':' + s);

	}, interval);
}

function update_status_timer(clock)
{
	var task_id = $(clock).parent().data('task-id'),
		time_assigned = $(clock).data('time-assigned'),
		eventTime = moment($(clock).data('started-on'), 'YYYY-MM-DD HH:mm:ss').unix(),
		currentTime = moment($(clock).data('now'), 'YYYY-MM-DD HH:mm:ss').unix(),
		diffTime = currentTime - eventTime,
		duration = moment.duration(diffTime * 1000, 'milliseconds'),
		interval = 1000;
		
	// Show $(clock)
	$(clock).html('<span class="label label-warning label-inprogress label-bordered"><?php e(lang('st_in_progress'))?></span> ');

	var $d = $('<span class="days" ></span>').appendTo($(clock)),
		$h = $('<span class="hours" ></span>').appendTo($(clock)),
		$m = $('<span class="minutes" ></span>').appendTo($(clock)),
		$s = $('<span class="seconds" ></span>').appendTo($(clock));

		update_status_timer_intervals[task_id] = setInterval(function(){

			duration = moment.duration(duration.asMilliseconds() + interval, 'milliseconds');
			var d = moment.duration(duration).days(),
				h = moment.duration(duration).hours(),
				m = moment.duration(duration).minutes(),
				s = moment.duration(duration).seconds();

			// Time alotted for Task
			if (duration.asMinutes() >= time_assigned) {
				if (update_status_timer_intervals[task_id]) {
					clearInterval(update_status_timer_intervals[task_id]);

					$.getJSON('<?php echo site_url('step/resolve_task/') ?>' + task_id, (data) => {
						if (data.message != '') {
							$.notify({
								message: data.message
							}, {
								type: data.message_type,
								z_index: 1051
							});
						}

						$('#resolve-task .modal-content').html(data.modal_content);
						$('#resolve-task').modal({backdrop: 'static'});
					});
				}
			}

			d = d == '0' ? '' : d + (d > 1 ? ' <?php e(lang('st_days'))?> ' : ' <?php e(lang('st_day'))?> ');
			h = h == '0' ? '' : h + (h > 1 ? ' <?php e(lang('st_hours'))?> ' : ' <?php e(lang('st_hour'))?> ');
			m = m == '0' ? '' : m + (m > 1 ? ' <?php e(lang('st_minutes'))?> ' : ' <?php e(lang('st_minute'))?> ');
			s = s == '0' ? '' : s + (s > 1 ? ' <?php e(lang('st_seconds'))?>' : ' <?php e(lang('st_second'))?>');

			$d.text(d);
			$h.text(h);
			$m.text(m);
			$s.text(s);

		}, interval);
}

function update_skip_votes()
{
	$.get('<?php e(site_url('step/get_skip_votes/'))?>' + $('.step-monitor').data('step-id'), (result) => {
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

		$.each(data.data, (index, item) => {
			old_vote = parseInt($('#task-' + item.task_id + ' .skip-votes').text());
			$('#task-' + item.task_id + ' .skip-votes').text(item.skip_votes);

			if (item.skip_votes != old_vote) {
				$('#task-' + item.task_id + ' .skip-votes').effect("highlight", {}, 3000);
			}
		});
	});
}