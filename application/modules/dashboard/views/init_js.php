// Enable jQuery tooltip
$('[data-toggle="tooltip"]').tooltip();

$('#calendar-init').fullCalendar({
	header: false,
	eventLimit: true, // allow "more" link when too many events
	firstDay: 1, // Monday
	height: 500,
	viewRender: function(view) {
		var title = view.title;
		$("#calendar-init-title").html(title);
	},
	loading: function (isLoading) {
		if (isLoading) {
			$('.calendar-init-wrapper').addClass('loading');
		} else {
			$('.calendar-init-wrapper').removeClass('loading');
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