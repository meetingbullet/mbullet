var INIT_DATA = {
	currentStep: 10,
	currentStepIndex: 0,
	path: null,
	bigestChallenge: null,
	meetings: {},
	events: []
};

var STEPS = [10, 20, 30, 31, 32, 33, 40, 50, 60];
var currentEvent = null;
var todoAttachment = [];
var agendaAttachment = [];

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

		// Gray out events which are not in current month
		if (event.end.format('M') != $('#calendar-init').fullCalendar('getDate').format('M')) {
			$(element).addClass('fc-other-month');
		}

		if (INIT_DATA.path) {
			if (INIT_DATA.path == 'guest') {
				// Remove all Owner meeting
				if (event.isOwner == true && event._id !== undefined) {
					if(! element.hasClass('hidden')) element.addClass('hidden');
				}
			} else {
				// Remove all Guest meeting
				if (event.isOwner == false && event._id !== undefined) {
					if(! element.hasClass('hidden')) element.addClass('hidden');
				}
			}
		}
	},
	viewRender: function(view) {
		var title = view.title;
		$("#calendar-init-title .month").html(title.split(' ')[0]);
		$("#calendar-init-title .year").html(title.split(' ')[1]);
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
			if (INIT_DATA.currentStepIndex == 1) {
				$('.btn-next-step').prop('disabled', false);
			}
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
	$(this).addClass('fc-state-active');

	updateOverview();
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

// Next step
$('.btn-next-step').click(function() {
	if (INIT_DATA.currentStepIndex + 1 >= STEPS.length) return;

	// Next Goal, Todo, Agenda, Team
	if (INIT_DATA.currentStep == 33 
		&& INIT_DATA.path == 'owner'
		&& $('.btn-wide.passed').length < $('.btn-wide').length) {
		for (var i in $('.btn-wide')) {
			if ( ! $($('.btn-wide')[i]).hasClass('passed')) {
				$($('.btn-wide')[i]).click();
				$($('.btn-wide')[i]).addClass('passed');
				break;
			}
		}

		return;
	}

	INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];

	//console.log('STEP:', INIT_DATA.currentStep, '\nIndex: ', INIT_DATA.currentStepIndex)

	if (INIT_DATA.currentStep >= 31 && INIT_DATA.currentStep <= 33) {
		if ($('#init .sub-step .dot.passed').length === 0) {
			$('#init .sub-step .dot:first-child').addClass('passed');
		} else {
			$('#init .sub-step .dot.passed + .dot').addClass('passed');
		}
	} else {
		$('#init .step.passed + .step').addClass('passed');
	}

	if (INIT_DATA.currentStep != 33) {
		$('.btn-next-step').prop('disabled', true);
	}

	if (INIT_DATA.currentStep == 32 && INIT_DATA.path == 'guest') {
		INIT_DATA.currentStepIndex = 6;
		INIT_DATA.currentStep = 40;
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
			if (INIT_DATA.path == 'owner') { // Path: Owner

				insertCurrentEvent();
				
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
		case 40:

			$.post("<?php echo site_url('/meeting/init_project') ?>", {
				data: JSON.stringify(INIT_DATA)
			}).done(function(data) {
				data = JSON.parse(data);
				// console.log(data);

				$('.init').addClass('hide-summary');
				$('.init .config .config-content').html(data.modal_content);
				$('.init #previous-step, .init-footer.calendar button').removeAttr('disabled');
			})

			$('#init .init-body .calendar').fadeOut(400, function() {
				$('#init .init-body .config').fadeIn();
			});
			break;
	}

	
});

$('.btn-underdog').click(function() {
	$('#init .step-20').removeClass('in');
	$('#init .init').removeClass('blur');
	$('.init-nav.summary .title').text("<?php echo lang('db_my_meetings_guest') ?>");
	
	INIT_DATA.path = 'guest';
	INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];
	//console.log('STEP:', INIT_DATA.currentStep, '\nIndex: ', INIT_DATA.currentStepIndex)

	// Remove all Owner meeting -> move to eventRender
	$('#calendar-init').fullCalendar('refetchEvents');

	$('#init .step-10').slideUp();
	$('#init .step-30 .guest').slideDown();
});

$('.btn-like-a-boss').click(function() {
	$('#init .step-20').removeClass('in');
	$('.init').removeClass('blur');
	$('.init-nav.summary .title').text("<?php echo lang('db_my_meetings_owner') ?>");
	
	INIT_DATA.path = 'owner';
	INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];
	//console.log('STEP:', INIT_DATA.currentStep)

	// Remove all Guest meeting -> move to eventRender
	$('#calendar-init').fullCalendar('refetchEvents');

	$('#init .step-10').slideUp();
	$('#init .step-30 .owner').slideDown();
});

$('.btn-skip-init').click(function() {
	$.get("<?php echo site_url('dashboard/skip_setup') ?>", (data) => {
		data = JSON.parse(data);
		$.mbNotify(data.message, data.message_type);
	})
});

$('.btn-wide').click(function(e) {
	e.preventDefault();
	if ($(this).hasClass('selected') ) return;

	var type = $(this).data('type');

	$('.define:visible').slideUp();
	$('.define-' + type).slideDown();

	$('.btn-wide').removeClass('selected');
	$(this).addClass('selected');
	$(this).addClass('passed');
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

	$('.table-wrapper.goal').slideDown();

	INIT_DATA.meetings[currentEvent.eventId].goal[index] = {
		name,
		type,
		importance
	}

	$('#goal-name').val('');
	$('#goal-type').prop('selectedIndex', 0);
	$('#goal-importance').prop('selectedIndex', 0);

	$('.btn-wide[data-type="goal"] > span').text(index + 1);
})

$('.init .define-homework .input-attachment').change(function(e) {
	var filename = e.target.files[0].name;
	var fileObject = $(this).clone();
	var i = todoAttachment.length; // Attachment index

	fileObject.filename = filename;
	todoAttachment.push(fileObject);

	$(this).before(`
	<div class="init-attachment" data-index="${i}">
		<i class="ion-document"></i>
		${filename}
		<i class="ion-android-close delete pull-right"></i>
	</div>
	`);

	$(this).val('');
})

$('.init .define-agenda .input-attachment').change(function(e) {
	var filename = e.target.files[0].name;
	var fileObject = $(this).clone();
	var i = agendaAttachment.length; // Attachment index

	fileObject.filename = filename;
	agendaAttachment.push(fileObject);

	$(this).before(`
	<div class="init-attachment" data-index="${i}">
		<i class="ion-document"></i>
		${filename}
		<i class="ion-android-close delete pull-right"></i>
	</div>
	`);

	$(this).val('');
})

$('.btn-create-todo').click(function(e){
	e.preventDefault();

	// Validation
	var err = false;
	$('#todo-name').removeClass('danger');
	$('#time-spent').removeClass('danger');
	$('#todo-assignee').removeClass('danger');


	if ( $('#todo-name').val().trim() == '' ) {
		$('#todo-name').addClass('danger');
		err = true;
	}

	if ( $('#time-spent').val() == '' ) {
		$('#time-spent').addClass('danger');
		err = true;
	}

	if ( $('#todo-assignee').val() == '' ) {
		$('#todo-assignee').addClass('danger');
		err = true;
	}

	if (err) return;

	var name = $('#todo-name').val();
	var time_spent = $('#time-spent').val();
	var assignee = $('#todo-assignee').val().split(',');
	var assignee_slash_total = assignee.length + '/' + currentEvent.attendees.length;
	var attachment_html = "";

	INIT_DATA.meetings[currentEvent.eventId].homework.push({
		name,
		time_spent,
		assignees: assignee,
		attachment: todoAttachment
	})

	todoAttachment.forEach((item) => {
		if (item) {
			attachment_html += `
				<span class="td-attachment" title="${item.filename}">
					<i class="ion-document"></i>
				</span>
			`;
		}
	})

	$(`
		<tr>
			<td>${name}</td>
			<td class="text-center">${attachment_html}</td>
			<td class="text-center">${assignee_slash_total}</td>
			<td class="text-center">${time_spent}</td>
		</tr>
	`).appendTo('.init .table-homework').effect('highlight', {}, 500);

	$('.init .td-attachment').tooltip();
	$('.table-wrapper.homework').slideDown();

	$(this).parents('form').find('.init-attachment').each(function() {
		$(this).slideUp(function(){
			$(this).remove();
		})
	})
	todoAttachment = [];

	$('#todo-name').val('');
	$('#time-spent').val('');
	$('#todo-assignee')[0].selectize.clear();

	$('.btn-wide[data-type="homework"] > span').text($('.table-homework tbody tr').length / 2);
});

$('.btn-create-agenda').click(function(e){
	e.preventDefault();

	// Validation
	var err = false;
	$('#agenda-name').removeClass('danger');
	$('#agenda-assignee').removeClass('danger');


	if ( $('#agenda-name').val().trim() == '' ) {
		$('#agenda-name').addClass('danger');
		err = true;
	}

	if ( $('#agenda-assignee').val() == '' ) {
		$('#agenda-assignee').addClass('danger');
		err = true;
	}

	if (err) return;

	var name = $('#agenda-name').val();
	var assignee = $('#agenda-assignee').val().split(',');
	var assignee_slash_total = assignee.length + '/' + currentEvent.attendees.length;
	var attachment_html = "";

	INIT_DATA.meetings[currentEvent.eventId].agenda.push({
		name,
		assignees: assignee,
		attachment: agendaAttachment
	})

	agendaAttachment.forEach((item) => {
		if (item) {
			attachment_html += `
				<span class="td-attachment" title="${item.filename}">
					<i class="ion-document"></i>
				</span>
			`;
		}
	})

	$(`
		<tr>
			<td>${name}</td>
			<td class="text-center">${attachment_html}</td>
			<td class="text-center">${assignee_slash_total}</td>
		</tr>
	`).appendTo('.init .table-agenda').effect('highlight', {}, 500);

	$('.init .td-attachment').tooltip();
	$('.table-wrapper.agenda').slideDown();

	$(this).parents('form').find('.init-attachment').each(function() {
		$(this).slideUp(function(){
			$(this).remove();
		})
	})
	agendaAttachment = [];

	$('#agenda-name').val('');
	$('#agenda-assignee')[0].selectize.clear();
	$('.btn-wide[data-type="agenda"] > span').text($('.table-agenda tbody tr').length / 2);

	// Create atleast 1 agenda to continue
	$('.btn-next-step').prop('disabled', false);
});

// Rating handler
$('.table-rate input[type="radio"]').change(function() {
	var type = $(this).prop('name');

	if (INIT_DATA.meetings[currentEvent.eventId].rate[type] === null) {
		$( $('#init .sub-step .dot:not(.passed)')[0] ).addClass('passed');
	}

	INIT_DATA.meetings[currentEvent.eventId].rate[type] = parseInt( $(this).val() );

	if (INIT_DATA.meetings[currentEvent.eventId].rate.meeting !== null
		&& INIT_DATA.meetings[currentEvent.eventId].rate.homework !== null
		&& INIT_DATA.meetings[currentEvent.eventId].rate.agenda !== null) {
		
		var avg = Math.round( 
			(
				INIT_DATA.meetings[currentEvent.eventId].rate.meeting +
				INIT_DATA.meetings[currentEvent.eventId].rate.homework +
				INIT_DATA.meetings[currentEvent.eventId].rate.agenda
			) / 3 
		);

		$('input[name="avg"] + label').prop('style', 'color:rgb(216, 216, 216)');
		$('#avg-star-' + avg).prop('checked', true);
		$('.avg-container *:not(input)').slideDown();
		$('.init .share-container').slideDown();
		$('.btn-next-step').prop('disabled', false);
	}
})

$('.btn-share-rating').popover({
	html: true, 
	placement: "left",
	content: function() {
		return $('#share-rating-popover').html();
	}
});

$(document).on('.btn-send-rating', 'click', function() {
	INIT_DATA.meetings[currentEvent.eventId].share_rating = $('#share-rating').val().trim().split(',');
	$('.btn-share-rating').popover('hide');
})

function enableAssigneeInput() {
	var target = 'input[name="assignee"]';

	if ($(target)[0].selectize) {
		$(target).each((i) => { 
			$(target)[i].selectize.clearOptions();
			$(target)[i].selectize.addOption(currentEvent.attendees);
		})
		return;
	}

	$(target).selectize({
		persist: true,
		maxItems: null,
		valueField: 'email',
		labelField: 'email',
		searchField: ['name', 'email'],
		options: currentEvent.attendees,
		render: {
			item: function(item, escape) {
				return '<div><span class="name">' + escape(item.email) + '</span>' +
				(item.responseStatus == 'accepted' 
				? " <span class='text-success'><i class='ion-checkmark'></i></span>"
				: '')
				+'</div>';
			},
			option: function(item, escape) {
				return '<div><span class="name">' + escape(item.email) + '</span>' +
				(item.responseStatus == 'accepted' 
				? " <span class='text-success'>(<?php echo lang('db_accepted')?>)</span>"
				: '')
				+'</div>';
			}
		}
	});
}


$(document).on('click', '.init-attachment .delete', function() {
	todoAttachment[$(this).parent().data('index')] = null;

	$(this).parent().slideUp(function(){
		$(this).remove();
	});
})

$(document).on('click', '.init .remove-team', function() {
	var email = $(this).parents('tr').data('email');

	for (var i in currentEvent.attendees) {
		if (currentEvent.attendees[i].email == email) {
			currentEvent.attendees.splice(i, 1);
			break;
		}
	}

	for (var i in INIT_DATA.meetings[currentEvent.eventId].members) {
		if (INIT_DATA.meetings[currentEvent.eventId].members[i] == email) {
			INIT_DATA.meetings[currentEvent.eventId].members.splice(i, 1);
			break;
		}
	}

	for (var i in INIT_DATA.meetings[currentEvent.eventId].homework) {
		for (var j in INIT_DATA.meetings[currentEvent.eventId].homework[i].assignees) {
			if (INIT_DATA.meetings[currentEvent.eventId].homework[i].assignees[j] == email) {
				INIT_DATA.meetings[currentEvent.eventId].homework[i].assignees.splice(j, 1);
				break;
			}
		}
	}

	for (var i in INIT_DATA.meetings[currentEvent.eventId].agenda) {
		for (var j in INIT_DATA.meetings[currentEvent.eventId].agenda[i].assignees) {
			if (INIT_DATA.meetings[currentEvent.eventId].agenda[i].assignees[j] == email) {
				INIT_DATA.meetings[currentEvent.eventId].agenda[i].assignees.splice(j, 1);
				break;
			}
		}
	}

	$(this).parents('tr').slideUp(function(){
		$(this).remove();
	});

	$('.btn-wide[data-type="team"] > span').text(currentEvent.attendees.length);
})

$(document).on('click', '.init .fc-event', function(e){
	e.preventDefault();
	if ($(this).hasClass('fc-other-month')) {
		return false;
	}

	var event = INIT_DATA.events[$(this).data('index')];

	if ( $('#init .step-30 .owner:visible').length ) {
		console.log(event.attendees)
		$('.table-team tbody').html('');
		attendees = []
		for (var i in event.attendees) {
			if (event.attendees[i].responseStatus == 'declined') {
				continue;
			}

			attendees.push(event.attendees[i])

			// Don't show yourself
			if (event.attendees[i].email == event.ownerEmail) {
				continue;
			}

			$(`
				<tr data-email="${event.attendees[i].email}">
					<td>${event.attendees[i].email}</td>
					<td class="text-center">
						<a href="#" class="remove-team text-danger">
							<i class="ion-close"></i>
						</a>
					</td>
				</tr>
			`).appendTo('.table-team tbody');

			console.log($('.table-team tbody'))
		}

		event.attendees = attendees;
		

		currentEvent = event;
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

		enableAssigneeInput();
		$('.btn-wide[data-type="team"] > span').text(event.attendees.length - 1);

		
		
	} else if ( $('#init .step-30 .guest:visible').length ) {
		for (var i in event.attendees) {
			if (event.attendees[i].responseStatus == 'declined') {
				event.attendees.splice(i, 1);
				continue;
			}
		}
		
		currentEvent = event;

		// Reset selected meeting & rates
		INIT_DATA.meetings = {};
		$('.init .todo-rating label').attr('style', '');
		$('.init .todo-rating input').attr('style', '');
		$('.init .todo-rating input').prop('checked', false);
		$('.btn-next-step').prop('disabled', true);
		$('#init .sub-step .dot.passed:not(:first-child)').removeClass('passed');
		INIT_DATA.currentStepIndex = 3;
		insertCurrentEvent();

		$('.init .step-30 .guest .instruction').slideUp();
		$('.init .table-rate').slideDown();

		$('.init .fc-event').removeClass('selected');
		$(this).addClass('selected');
	}
});

/*
	Step 10: Update overview data on change calendar view
*/
function updateOverview()
{
	var events = $('#calendar-init').fullCalendar('clientEvents');
	var selectedDate = $('#calendar-init').fullCalendar('getDate');
	var currentView = $('.init .fc-change-view.fc-state-active').data('work');
	var startOfTime = selectedDate.clone().startOf(currentView);
	var endOfTime = selectedDate.clone().endOf(currentView);
	var savedEvents = [];
	var eventIDs = [];
	var MBEvents = [];

	var overviewData = {
		totalMeeting : 0,
		totalTime : 0,
		ownerMeeting : 0,
		ownerTime : 0,
		guestMeeting : 0,
		guestTime : 0,
		ownerMBMeeting : 0,
		ownerMBTime : 0,
		guestMBMeeting : 0,
		guestMBTime : 0,
		workX: $('.init .fc-change-view.fc-state-active').data('work-lang'),
		selectedRange: startOfTime.format('ddd MMM D') + ' - ' + endOfTime.format('ddd MMM D'),
	};


	switch (currentView) {
		case 'day':
			workingTime = 8;
			break;
		case 'week':
			workingTime = 40;
			break;
		case 'month':
			workingTime = calcNumberOfWorkingDay(selectedDate);
			break;
	}

	overviewData.Xhour = "<?php echo lang('db_x_hr') ?>".format(workingTime);

	// Check event imported into MB
	// Remove events which are not in current view
	for (var i in events) {
		if ( !( events[i].end.unix() >=  startOfTime.unix()
				&& events[i].end.unix() <= endOfTime.unix() ) ) 
		{
			delete events[i];
			continue;
		}

		if ( ! eventIDs[events[i].eventId]) {
			eventIDs.push(events[i].eventId);
		}
	}

	$.post("<?php echo site_url('dashboard/check_meeting_by_google_event_id') ?>", {eventIDs}, (data) => {
		MBEvents = JSON.parse(data);
		events.forEach(function(e, i) {
			var increaseTime = e.allDay ? 24 * 60 : (e.end - e.start) / 1000 / 60;
			overviewData.totalMeeting ++;
			overviewData.totalTime += increaseTime;

			if (e.isOwner === true) {
				overviewData.ownerMeeting ++;
				overviewData.ownerTime += increaseTime;

				if (MBEvents[e.eventId]) {
					overviewData.ownerMBMeeting ++;
					overviewData.ownerMBTime += increaseTime;
				}
			} else {
				overviewData.guestMeeting ++;
				overviewData.guestTime += increaseTime;

				if (MBEvents.indexOf(e.eventId) >= 0) {
					overviewData.guestMBMeeting ++;
					overviewData.guestMBTime += increaseTime;
				}
			}
		});

		overviewData.ownerNonMBMeeting = overviewData.ownerMeeting - overviewData.ownerMBMeeting;
		overviewData.guestNonMBMeeting = overviewData.guestMeeting - overviewData.guestMBMeeting;
		overviewData.ownerNonMBTime = overviewData.ownerTime - overviewData.ownerMBTime;
		overviewData.guestNonMBTime = overviewData.guestTime - overviewData.guestMBTime;
		overviewData.percentOfWorkingX =  Math.round(overviewData.totalTime / 60 / workingTime * 100);

		for (var key in overviewData) {
			// Convert Time to Hour
			if (key.indexOf('Time') >= 0) {
				$('#init .' + key).data('minute', overviewData[key]);
				$('#init .' + key).text(Math.round(overviewData[key] * 10 / 60) / 10);
			} else {
				$('#init .' + key).text(overviewData[key]);
			}
		}
	});

}

function insertCurrentEvent()
{
	INIT_DATA.meetings[currentEvent.eventId] = {
		name: currentEvent.title,
		scheduled_start_time: currentEvent.start.format("YYYY-MM-DD HH:mm:ss"),
		in: (currentEvent.end - currentEvent.start) / 1000 / 60,
		owner: currentEvent.ownerEmail,
		members: [],
		goal: [],
		homework: [],
		agenda: [],
		team: [],
		rate: {
			meeting: null,
			homework: null,
			agenda: null
		}
	};

	currentEvent.attendees.forEach((person) => {
		INIT_DATA.meetings[currentEvent.eventId].members.push(person.email);
	})
}

function calcNumberOfWorkingDay(moment) 
{
	var cntDay = 0,
		curDay = moment.clone().startOf('month'),
		endOfMonth = moment.clone().endOf('month');
	
	do {
		if (curDay.format('dddd') != 'Saturday' && curDay.format('dddd') != 'Sunday'){
			cntDay++;
		}

		curDay.add(1, 'days');
	} while (curDay.format('D') != endOfMonth.format('D'));

	return cntDay * 8; // 8 hours per day
}

// ********start - baodg********
$(document).on('click', '#init .init-body .init-project .action .delete-meeting', function() {
	var that = $(this);
	if ($('#init .init-body .calendar .init-project .action .delete-meeting').length <= 1) {
		swal("Warning", "We need at least 1 meeting to import", "warning");
		return;
	}

	var event_id = that.closest('tr').data('event-id');
	delete INIT_DATA.meetings[event_id];

	that.closest('tr')
		.find('td')
		.wrapInner('<div style="display: block;" />')
		.parent()
		.find('td > div')
		.slideUp('fast', function(){
			$(this).parent().parent().remove();
		});
});

$(document).on('change', '#init .init-body .init-project select', function() {
	var that = $(this);
	var project_id = that.val();
	var event_id = that.closest('tr').data('event-id');
	INIT_DATA.meetings[event_id].project_id = project_id;
});

$(document).on("click", '#init-create-project-modal form button[type=submit]', function(e) {
	e.preventDefault();

	var method = $(this).closest('form').attr('method') ? $(this).closest('form').attr('method') : 'post';
	var data = $(this).closest('form').serialize();

	// Since serialize does not include form's action button, 
	// we need to add it on our own.
	data += '&' + $(this).attr('name') + '=';

	if (typeof(INIT_DATA.new_projects_count) == 'undefined') {
		INIT_DATA.new_projects_count = 0;
	}

	$.ajax({
		type: "POST",
		url: $(this).closest('form').attr('action'),
		data: data,
		success: (data) => {
			data = JSON.parse(data);

			if (data.close_modal === 0) {
				$('#init-create-project-modal .modal-content').html(data.modal_content);
			} else {
				$('#init-create-project-modal').modal('hide');
			}

			if (data.message_type) {
				$.mbNotify(data.message, data.message_type);

				if (data.message_type == 'success') {
					// New meeting created, refresh modal;
					INIT_DATA.new_projects_count += 1;

					$.post('<?php echo site_url('/meeting/init_project') ?>', {
						data: JSON.stringify(INIT_DATA)
					}).done(function(refresh_data) {
						refresh_data = JSON.parse(refresh_data);

						$('#init .init-body .config .content-container').html(refresh_data.modal_content);
					});
				}
			}
		}
	});
});

$(document).on('click', '#init .init-footer.calendar #previous-step, #init .init-footer.calendar #next-step', function() {
	var screen_url = {
		'40': '<?php echo site_url('/meeting/init_project') ?>',
		'50': '<?php echo site_url('/meeting/init_team') ?>',
		'60': '<?php echo site_url('/meeting/init_finish') ?>',
	};
	$('#init .init-body .config .content-container').fadeOut();
	$('#init .init-footer.calendar #previous-step, #init .init-footer.calendar button').attr('disabled', 'disabled');

	var that = $(this);

	if (INIT_DATA.currentStep == 60) {
		$('#init #attachment-form').submit();
		swal({
			title: "Importing...",
			text: "Do not close the window until the process is done!",
			type: "warning",
			showCancelButton: false,
			showConfirmButton: false
		});

		var isFinished = true;
	}

	if (that.attr('id') == 'next-step' && INIT_DATA.currentStep < 60) {
		INIT_DATA.currentStep = STEPS[++INIT_DATA.currentStepIndex];
	}

	if (that.attr('id') == 'previous-step') {
		INIT_DATA.currentStep = STEPS[--INIT_DATA.currentStepIndex];

		if (INIT_DATA.currentStep < 40) {
			if (INIT_DATA.path == 'owner') {

			} else {

			}

			$('#init .init-body .config, .init-footer.calendar .init-footer-content > *').fadeOut(400, function() {
				
				$('#init .init-body .calendar').fadeIn();
			});
			$('.init').removeClass('hide-summary');
		}
	}

	if (INIT_DATA.currentStep == 50 && INIT_DATA.path == 'owner') {
		$('#init #attachment-form').html();
		for (var event_id in INIT_DATA.meetings) {
			for (var i = 0; i < INIT_DATA.meetings[event_id].agenda.length; i++) {
				for (var j = 0; j < INIT_DATA.meetings[event_id].agenda[i].attachment.length; j++) {
					INIT_DATA.meetings[event_id].agenda[i].attachment[j].data('name', `agenda[${event_id}][${i}][${j}]`)
					$('#init #attachment-form').append(INIT_DATA.meetings[event_id].agenda[i].attachment[j]);
				}
			}

			for (var i = 0; i < INIT_DATA.meetings[event_id].homework.length; i++) {
				for (var j = 0; j < INIT_DATA.meetings[event_id].homework[i].attachment.length; j++) {
					INIT_DATA.meetings[event_id].homework[i].attachment[j].data('name', `homework[${event_id}][${i}][${j}]`)
					$('#init #attachment-form').append(INIT_DATA.meetings[event_id].homework[i].attachment[j]);
				}
			}
		}
	}

	if (INIT_DATA.currentStep == 60) {
		$('#init .init-footer.calendar #next-step').text('Import');
	} else {
		$('#init .init-footer.calendar #next-step').text('Next');
	}

	var url = screen_url[INIT_DATA.currentStep.toString()];

	$.post(url, {
		data: JSON.stringify(INIT_DATA)
	}).done(function(data) {
		data = JSON.parse(data);

		$('#init .init-body .config .content-container .config-content').html(data.modal_content);
		$('#init .init-footer.calendar #previous-step, #init .init-footer.calendar button').removeAttr('disabled');
		$('#init .init-body .config .content-container').fadeIn();

		if (typeof isFinished !== undefined && isFinished == true) {
			swal.close();
			$('#init').modal('hide');
			$('.calendar-wrapper').removeClass('blur');
			$.mbNotify('Import successfully', 'success');
		}
	}).fail(function() {
		//console.log('failed');
	});
});

$(document).on('submit','#init #attachment-form', function(e) {
	e.preventDefault();
	var that = $(this);

	var fd = new FormData();
	$('#init #attachment-form input[type="file"]').each(function() {
		var this_input_file = $(this);
		var file_data = this_input_file[0].files; // for multiple files
		for(var i = 0; i < file_data.length; i++){
			var name = this_input_file.attr('name');
			fd.append(this_input_file.data('name'), file_data[i]);
		}
	});


	var other_data = $('#init #attachment-form').serializeArray();
	other_data.push({
		name: 'data',
		value: JSON.stringify(INIT_DATA) 
	});
	$.each(other_data,function(key,input){
		fd.append(input.name,input.value);
	});

	$.ajax({
		url: that.attr('action'),
		data: fd,
		contentType: false,
		processData: false,
		type: 'POST',
		success: function(data){
			//console.log(data);
		}
	});
});

$(document).on('hidden.bs.modal', '#init.modal', function() {
	location.reload();
});
// ********end - baodg********