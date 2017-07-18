var INIT_DATA = {
	currentStep: 10,
	currentStepIndex: 0,
	path: null,
	bigestChallenge: null,
	meetings: [],
	events: []
};

var STEPS = [10, 20, 30, 31, 32, 33, 40, 50, 60];
var currentEvent = null;

// Enable jQuery tooltip
$('[data-toggle="tooltip"]').tooltip();

$('#calendar-init').fullCalendar({
	header: false,
	eventLimit: true, // allow "more" link when too many events
	firstDay: 1, // Monday
	height: 500,
	cache: true,
	eventRender: function(event, element) {
		i = INIT_DATA.events.length;
		INIT_DATA.events[i] = event;
		element.data('index', i);
	},
	viewRender: function(view) {
		var title = view.title;
		$("#calendar-init-title").html(title);
	},
	loading: function (isLoading) {
		$('.calendar-init-wrapper').toggleClass('loading');

		// Step 1: Passed
		if (! isLoading && ! $('#init .step.setup').hasClass('passed')) {
			INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];
			$('#init .step.setup').addClass('passed');
		}

		// Update overview after switchs to new month
		if (! isLoading) {
			updateOverview();
			$('.btn-next-step').prop('disabled', false);
		}
	},
	events: {
		url: "<?php echo site_url('meeting/get_events/ggc?init=vit') ?>",
		error: function() {
			$.mbNotify("<?php echo lang('db_unable_to_fetch_event_from_google_calendar') ?>", 'danger');
		}
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

$('.bigest-challenge .answer').click(function() {
	INIT_DATA.bigestChallenge = $(this).data('answer');
	$('.bigest-challenge .answer').removeClass('selected');
	$(this).addClass('selected');
	$('.btn-next-step').prop('disabled', false);
})

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
	if (INIT_DATA.currentStepIndex + 1 >= STEPS.length) return;

	INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];
	console.log('STEP:', INIT_DATA.currentStep, '\nIndex: ', INIT_DATA.currentStepIndex)

	if (INIT_DATA.currentStep >= 31 && INIT_DATA.currentStep <= 33) {
		if ($('#init .sub-step .dot.passed').length === 0) {
			$('#init .sub-step .dot:first-child').addClass('passed');
		} else {
			$('#init .sub-step .dot.passed + .dot').addClass('passed');
		}
	} else {

		$('#init .step.passed + .step').addClass('passed');
	}

	switch (INIT_DATA.currentStep) {
		case 30:
			$('#init .init').addClass('blur');
			$('#init .step-20').addClass('in');
			break;
		case 32:
			$('.bigest-challenge').slideDown();
			$('.btn-next-step').prop('disabled', true);
			break;
		case 33:
			INIT_DATA.meetings[currentEvent.eventId] = {
				name: currentEvent.title,
				scheduled_start_time: currentEvent.start.format("YYYY-MM-DD HH:mm:ss"),
				in: (currentEvent.end - currentEvent.start) / 1000 / 60,
				owner: currentEvent.ownerEmail,
				members: [],
				goal: [],
				homework: [],
				agenda: [],
				team: []
			};

			currentEvent.attendees.forEach((person) => {
				INIT_DATA.meetings[currentEvent.eventId].members.push(person.email);
			})

			if (INIT_DATA.path == 'owner') { // Path: Owner
				
				$('.step-30').slideUp();
				$('.step-32-sub').slideDown(() => {
					$('.step-32')
					.addClass('in')
					.show("slide", { 
						direction: "right", 
						easing: "easeOutQuint" 
					}, 400);
				});

				$('.calendar-wrapper').addClass('blur');
			} else { // Path: Guest
			

			}
			break;
	}

	$('.btn-next-step').prop('disabled', true);
});

$('.btn-underdog').click(function() {
	$('#init .step-20').removeClass('in');
	$('#init .init').removeClass('blur');
	$('.init-nav.summary .title').text("<?php echo lang('db_my_meetings_guest') ?>");
	
	INIT_DATA.path = 'guest';
	INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];
	console.log('STEP:', INIT_DATA.currentStep, '\nIndex: ', INIT_DATA.currentStepIndex)

	// Remove all Owner meeting
	$('#calendar-init').fullCalendar('clientEvents').forEach(function(item) {
		if (item.isOwner == true) {
			$('#calendar-init').fullCalendar('removeEvents', item._id);
		}
	});

	$('#init .step-10').slideUp();
	$('#init .step-30 .guest').slideDown();
});

$('.btn-like-a-boss').click(function() {
	$('#init .step-20').removeClass('in');
	$('.init').removeClass('blur');
	$('.init-nav.summary .title').text("<?php echo lang('db_my_meetings_owner') ?>");
	
	INIT_DATA.path = 'owner';
	INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];
	console.log('STEP:', INIT_DATA.currentStep, '\nIndex: ', INIT_DATA.currentStepIndex)

	// Remove all Guest meeting
	$('#calendar-init').fullCalendar('clientEvents').forEach(function(item) {
		if (item.isOwner == false) {
			$('#calendar-init').fullCalendar('removeEvents', item._id);
		}
	});

	$('#init .step-10').slideUp();
	$('#init .step-30 .owner').slideDown();
});

$('.btn-skip-init').click(function() {
	$.get("<?php echo site_url('dashboard/skip_setup') ?>", (data) => {
		data = JSON.parse(data);
		$.mbNotify(data.message, data.message_type);
	})
});

$('.btn-create-goal').click(function(e) {
	e.preventDefault();
	$('#goal-name').removeClass('danger');
	$('#goal-type').removeClass('danger');
	$('#goal-importance').removeClass('danger');
	var error = false;

	// Validation
	if ($('#goal-name').val().trim() == '') {
		$('#goal-name').addClass('danger');
		error = true;
	}

	if ($('#goal-type').val() === null) {
		$('#goal-type').addClass('danger');
		error = true;
	}

	if ($('#goal-importance').val() === null) {
		$('#goal-importance').addClass('danger');
		error = true;
	}

	if (error) return;

	var name = $('#goal-name').val().trim(),
	type = $('#goal-type').val(),
	type_lang = $('#goal-type option:selected').text(),
	importance = $('#goal-importance').val(),
	importance_lang = $('#goal-importance option:selected').text(),
	index = INIT_DATA.meetings[currentEvent.eventId].goal.length;

	$(`
		<tr data-index="${index}">
			<td><strong>${name}</strong></td>
			<td class="text-center ${type}">${type_lang}</td>
			<td class="text-center ${importance}">${importance_lang}</td>
		</tr>
	`).appendTo('.table-goal tbody').effect('highlight', {}, 500);

	INIT_DATA.meetings[currentEvent.eventId].goal[index] = {
		name,
		type,
		importance
	}

	$('#goal-name').val('');
	$('#goal-type').prop('selectedIndex', 0);
	$('#goal-importance').prop('selectedIndex', 0);

	$('.btn-define-goal > span').text(index);
})

$(document).on('click', '.init .fc-event', function(e){
	e.preventDefault();
	var event = INIT_DATA.events[$(this).data('index')];

	if ( $('#init .step-30 .owner:visible').length ) {
		currentEvent = event;
		console.log('event', event);
		var date = event.start.format('ddd MMM D') == event.end.format('ddd MMM D') ?
					event.start.format('ddd MMM D') :
					event.start.format('ddd MMM D') + ' - ' + event.end.format('ddd MMM D');

		var hour = (event.end - event.start) / 1000 / 60 / 60;
		$('#init .table-improve-meeting .name').text(event.title);
		$('#init .table-improve-meeting .date').text(date);
		$('#init .table-improve-meeting .time').text(event.start.format('hh:mma') + ' - ' + event.end.format('hh:mma'));
		$('#init .table-improve-meeting .team').text(event.attendees.length);

		$('#init .meeting-cost .hour').text("<?php echo lang('db_x_hrs') ?>".format(hour))
		$('#init .meeting-cost .total-participant').text("<?php echo lang('db_x_participants') ?>".format(event.attendees.length))
		$('#init .meeting-cost .total-hour').text("<?php echo lang('db_x_hrs') ?>".format(hour * event.attendees.length))

		$('#init .step-30 .owner .instruction').addClass('passed');
		$('#init .step-30 .owner .table-improve-meeting').slideDown();
		$('#init .step-30 .owner .table-improve-meeting tbody').effect('highlight', {}, 500);

		$('.init .fc-event').removeClass('selected');
		$(this).addClass('selected');
		$('.btn-next-step').prop('disabled', false);

	} else if ( $('#init .step-30 .guest:visible').length ) {

		$('.init .fc-event').removeClass('selected');
		$(this).addClass('selected');
	}
});

/*
	Step 31: Update overview data onclick Event
*/
function updateOverview()
{
	var events = $('#calendar-init').fullCalendar('clientEvents');
	var savedEvents = [];

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

/*
	sprintf() for JavaScript.
	Grabs from https://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format
	Usage: "{0} is dead, but {1} is alive! {0} {2}".format("ASP", "ASP.NET")
*/
if (!String.prototype.format) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) { 
		return typeof args[number] != 'undefined'
			? args[number]
			: match
		;
		});
	};
}