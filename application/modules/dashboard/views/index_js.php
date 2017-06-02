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
