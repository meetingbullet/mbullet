// Open meeting monitor
$('.btn-open-meeting-monitor').click((e) => {
	e.preventDefault();
	var key = $(e.target).data('meeting-key') ? $(e.target).data('meeting-key') : $(e.target).parent().data('meeting-key');

	if (key == undefined) {
		console.error('Unable to get STEP KEY on target', $(e.target));
		return;
	}

	// Adjust diff between server and client on counters
	$(document).data('ajax-start-time', moment().unix());

	$.get('<?php e(site_url('meeting/monitor/')) ?>' + key, (data) => {
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

	if ($(this).hasClass('meeting-open')) {
		$(this).removeClass('meeting-open');
		$(this).find('span').text('<?php echo lang('st_monitor')?>')
	}
});

$('.meeting-timer.ready').each((i, item) => {
	var eventTime = moment($(item).data('scheduled-start-time'), 'YYYY-MM-DD HH:mm:ss').unix(),
		currentTime = moment($(item).data('now'), 'YYYY-MM-DD HH:mm:ss').unix(),
		diffTime = currentTime - eventTime,
		duration = moment.duration(diffTime * 1000, 'milliseconds');

	if (diffTime <= 0) {
		if ($(item).parent().find('.btn-open-meeting-monitor').hasClass('is-owner')) {
			$(item).parent().find('.btn-open-meeting-monitor').text('<?php e(lang('st_start'))?>');
			$(item).parent().find('.btn-open-meeting-monitor').removeClass('hidden');
		} else {
			$(item).text('<?php e(lang('st_waiting_for_start'))?>');
		}

		return;
	}

	var d = moment.duration(duration).days(),
		h = moment.duration(duration).hours(),
		m = moment.duration(duration).minutes(),
		s = moment.duration(duration).seconds();

	d = d == '0' ? '' : d + 'd';
	h = h == '0' ? '' : h + 'h';
	m = m == '0' ? '' : m + 'm';
	s = s == '0' ? '' : s + 's';

	$(item).text('<?php e(lang('st_in')) ?> ' + d + ' ' + h + ' ' + m);

	if ($(item).text().trim() == 'in') {
		$(item).text('');
	}
});

// Calendar
$('#meeting-calendar').fullCalendar({
	header: {
		center: 'prev, today, next ',
		left: 'title',
		right: 'month,agendaWeek,agendaDay,listWeek'
	},
	
	navLinks: true,
	firstDay: 1, // Monday
	aspectRatio: 1, // content Width-to-Height
	editable: false,
	eventLimit: true, // allow "more" link when too many events
	events: <?php echo json_encode($meeting_calendar) ?>
});

// rating
$(".todo-rating label").click(function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('input[type=radio]').removeAttr('checked');
	$(this).parent().find('input[type=radio]#' + input_id).attr('checked', '');
});


$(document).ready(function() {
	$(".my-todo").on("click", ".submit", function(e) {
		e.preventDefault();
		var submit_btn = $(this);

		if (submit_btn.parent().parent().hasClass('homework')) {
			var todo_type = 'homework';
		}

		if (submit_btn.parent().parent().hasClass('evaluate')) {
			var todo_type = 'evaluate';
		}

		if (submit_btn.parent().parent().hasClass('decide')) {
			var todo_type = 'decide';
		}

		if (todo_type == 'homework' || todo_type == 'evaluate') {
			var url = submit_btn.parent().data('url');

			if (todo_type == 'homework') {
				var data = {};
				data.pk = submit_btn.parent().data('homework-id');
				data.value = submit_btn.data('status');
				data.name = 'status'
			}

			if (todo_type == 'evaluate') {
				var data = {};
				data.rate = submit_btn.parent().find('input[type=radio]:checked').val();

				if (typeof(data.rate) != 'undefined') {
					data.meeting_id = submit_btn.parent().data('meeting-id');

					if (submit_btn.parent().hasClass('user')) {
						data.user_id = submit_btn.parent().data('user-id');
					}

					if (submit_btn.parent().hasClass('agenda')) {
						data.agenda_id = submit_btn.parent().data('agenda-id');
					}
				} else {
					var error = '<?php echo lang("db_rate_needed") ?>';
				}
			}
			if (typeof(error) == 'undefined') {
				$.post({
					url: url,
					data: data,
				}).done(function(data) {console.log(data);
					data = JSON.parse(data);
					if (data.message_type == 'success') {
						submit_btn.parent().parent().slideUp();
					}

					$.notify({
						message: data.message
					}, {
						type: data.message_type,
						z_index: 1051
					});
				}).fail(function(xhr, statusText) {
					console.log(xhr.status);
					$.notify({
						message: data.message
					}, {
						type: data.message_type,
						z_index: 1051
					});
				});
			} else {
				$.notify({
					message: error
				}, {
					type: 'danger',
					z_index: 1051
				});
			}
		}
	});
})

// Decide
$('.submit-confirm-status').click(function(e) {
	e.preventDefault();

	var confirm_status_selector = $(this).parent().find('select[name="confirm-status"]');
	var value = $(this).parent().find('select[name="confirm-status"] option:selected').val();
	var pk = $(confirm_status_selector).data('pk');

	if (value != '') {
		$(confirm_status_selector).removeClass('danger');

		$.post('<?php echo site_url("agenda/ajax_edit") ?>', {
			pk,
			name: "confirm_status",
			value
		}, (data) => {
			data = JSON.parse(data);

			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});

			if (data.message_type == 'success') {
				console.log('Decided, closing');
				$(this).parents('.item').slideUp();
			}
		});
	} else {
		$(confirm_status_selector).addClass('danger');
	}
});

$('select[name="confirm-status"]').change(function() {
	if ( $(this).children('option:selected').val() != '') {
		$(this).removeClass('danger');
	}
});
