// Open step monitor
$('.btn-open-step-monitor').click((e) => {
	var key = $(e.target).data('step-key');
	e.preventDefault();

	$.get('<?php e(site_url('step/monitor/')) ?>' + key, (data) => {
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

$('.step-timer.ready').each((i, item) => {
	var eventTime = moment($(item).data('scheduled-start-time'), 'YYYY-MM-DD HH:mm:ss').unix(),
		currentTime = moment($(item).data('now'), 'YYYY-MM-DD HH:mm:ss').unix(),
		diffTime = currentTime - eventTime,
		duration = moment.duration(diffTime * 1000, 'milliseconds');

	if (diffTime <= 0) {
		if ($(item).parent().find('.btn-open-step-monitor').hasClass('is-owner')) {
			$(item).parent().find('.btn-open-step-monitor').text('<?php e(lang('st_start'))?>');
			$(item).parent().find('.btn-open-step-monitor').removeClass('hidden');
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