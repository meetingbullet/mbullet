// Enable jQuery tooltip
$('[data-toggle="tooltip"]').tooltip();

$('#calendar-init').fullCalendar({
	header: false,
	eventLimit: true, // allow "more" link when too many events
	firstDay: 1, // Monday
	height: 500,
	cache: true,
	viewRender: function(view) {
		var title = view.title;
		$("#calendar-init-title").html(title);
	},
	loading: function (isLoading) {
		$('.calendar-init-wrapper').toggleClass('loading');

		// Step 1: Passed
		if (! isLoading && ! $('#init .step.setup').hasClass('passed')) {
			INIT_DATA.currentStep = 20;
			$('#init .step.setup').addClass('passed');
		}

		// Update overview after switchs to new month
		if (! isLoading) {
			updateOverview();
		}
	},
	events: {
		url: "<?php echo site_url('meeting/get_events/ggc') ?>",
		error: function() {
			$.mbNotify("<?php echo lang('db_unable_to_fetch_event_from_google_calendar') ?>", 'danger');
		},
		color: 'yellow',   // a non-ajax option
		textColor: 'black' // a non-ajax option
    }
});

// Fix bug FullCalendar vs BS.Modal
window.setTimeout(() => {
	$('#calendar-init').fullCalendar('render');
}, 250);

// Calendar action handler
$('.calendar-info .fc-today-button').click(function() {
	$('#calendar-init').fullCalendar('today')
})

$('.calendar-info .fc-prev-button').click(function() {
	$('#calendar-init').fullCalendar('prev')
})

$('.calendar-info .fc-next-button').click(function() {
	$('#calendar-init').fullCalendar('next')
})

$('.calendar-info .fc-full-button').click(function() {
	$(this).addClass('fc-state-active');
	$('.calendar-info .fc-list-button').removeClass('fc-state-active')

	$('.calendar-info .fc-change-view.fc-state-active').click()
})
$('.calendar-info .fc-list-button').click(function() {
	$(this).addClass('fc-state-active');
	$('.calendar-info .fc-full-button').removeClass('fc-state-active')

	$('.calendar-info .fc-change-view.fc-state-active').click()
})

$('.calendar-info .fc-change-view').click(function() {
	var type = $('.fc-full-button').hasClass('fc-state-active') ? 'full' : 'list';
	var view = $(this).data(type + '-view')

	$('#calendar-init').fullCalendar('changeView', view)
	$('.calendar-info .fc-change-view').removeClass('fc-state-active')
	$(this).addClass('fc-state-active')
})

function updateOverview()
{
	var savedEvents = [];
	var events = $('#calendar-init').fullCalendar('clientEvents');

	var oData = {
		totalMeeting : 0,
		totalTime : 0,
		ownerMeeting : 0,
		ownerTime : 0,
		guestMeeting : 0,
		guestTime : 0,
		ownerMBMeeting : 0,
		ownerMBTime : 0,
		guestMBMeeting : 0,
		guestMBTime : 0
	};

	// Check event imported into MB
	var eventIDs = [];
	var MBEvents = [];
	events.forEach((e) => {
		if ( ! eventIDs[e.eventId]) {
			eventIDs.push(e.eventId);
		}
	});

	$.post("<?php echo site_url('dashboard/check_meeting_by_google_event_id') ?>", {eventIDs}, (data) => {
		MBEvents = JSON.parse(data);
		events.forEach(function(e, i) {
			var increaseTime = e.allDay ? 24 * 60 : (e.end - e.start) / 1000 / 60;
			oData.totalMeeting ++;
			oData.totalTime += increaseTime;

			if (e.isOwner === true) {
				oData.ownerMeeting ++;
				oData.ownerTime += increaseTime;

				if (MBEvents[e.eventId]) {
					oData.ownerMBMeeting ++;
					oData.ownerMBTime += increaseTime;
				}
			} else {
				oData.guestMeeting ++;
				oData.guestTime += increaseTime;

				if (MBEvents.indexOf(e.eventId) >= 0) {
					oData.guestMBMeeting ++;
					oData.guestMBTime += increaseTime;
				}
			}
		});

		oData.ownerNonMBMeeting = oData.ownerMeeting - oData.ownerMBMeeting;
		oData.guestNonMBMeeting = oData.guestMeeting - oData.guestMBMeeting;
		oData.ownerNonMBTime = oData.ownerTime - oData.ownerMBTime;
		oData.guestNonMBTime = oData.guestTime - oData.guestMBTime;
		oData.percentOfWorkingHour =  Math.ceil(oData.totalTime / 60 / 40 * 100);

		for (var key in oData) {
			// Convert Time to Hour
			if (key.indexOf('Time') >= 0) {
				$('#init .' + key).data('minute', oData[key]);
				$('#init .' + key).text(Math.round(oData[key] * 10 / 60) / 10);
			} else {
				$('#init .' + key).text(oData[key]);
			}
		}
	});

}

$('.btn-convert-time + ul > li > a').click(function() {
	$('.btn-convert-time .text').text($(this).text());

	switch ($(this).data('option')) {
		case 'minute':
			$('#init .target-time').each((i, item) => {
				$(item).text($(item).data('minute'))
			});
			break;
		case 'hour':
			$('#init .target-time').each((i, item) => {
				$(item).text(Math.round($(item).data('minute') * 10 / 60) / 10)
			});
			break;
		case 'day':
			$('#init .target-time').each((i, item) => {
				$(item).text(Math.round($(item).data('minute') * 100 / 60 / 24) / 100)
			});
			break;
	}
});

$('.btn-next-step').click(function() {
	if (INIT_DATA.currentStep > 60) return;

	if (INIT_DATA.currentStep >= 30 && INIT_DATA.currentStep <= 31) {
		INIT_DATA.currentStep ++;
		if ($('#init .sub-step .dot.passed').length === 0) {
			$('#init .sub-step .dot:first-child').addClass('passed');
		} else {
			$('#init .sub-step .dot.passed + .dot').addClass('passed');
		}


	} else {
		INIT_DATA.currentStep += 10;

		if (INIT_DATA.currentStep == 42) {
			INIT_DATA.currentStep = 40;
		}

		$('#init .step.passed + .step').addClass('passed');
	}
});

$('.btn-skip-init').click(function() {
	$.get("<?php echo site_url('dashboard/skip_setup') ?>", (data) => {
		data = JSON.parse(data);
		$.mbNotify(data.message, data.message_type);
	})
});