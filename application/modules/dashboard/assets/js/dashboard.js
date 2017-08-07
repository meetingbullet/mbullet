$(document).ready(function() {
	// Create project function for testing, remove when finished
	$('#create').click((e) => {
		e.preventDefault();

		$.get(CREATE_PROJECT_URL, (data) => {
			data = JSON.parse(data);
			$('#bigModal .modal-content').html(data.modal_content);
			$('#bigModal').modal({backdrop: "static"});
		});

	});

	$('#invite').click((e) => {
		e.preventDefault();

		$.get(INVITE_USER_URL, (data) => {
			data = JSON.parse(data);
			$('#inviteModal .modal-content').html(data.modal_content);
			$('#inviteModal').modal({backdrop: "static"});
		});

	});

	// Set form-ajax to work inside a modal
	$(document).on("submit", '.form-ajax', (e) => {
		e.preventDefault();

		var method = $(e.target).attr('method') ? $(e.target).attr('method') : 'post';
		var data = $(e.target).serialize();

		// Temporary disable form's buttons to prevent duplicate requests
		$(e.target).find('button').prop('disabled', true);

		// Since serialize does not include form's action button, 
		// we need to add it on our own.
		data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

		$.ajax({
			type: "POST",
			url: $(e.target).attr('action'),
			data: data,
			success: (data) => {
				data = JSON.parse(data);

				if (data.close_modal === 0) {
					$('.modal .modal-content').html(data.modal_content);
				} else {
					$('.modal').modal('hide');
				}

				if (data.message_type) {
					$.mbNotify(data.message, data.message_type);

					if (data.message_type == 'success') {
						// New project created, insert to project list
						// $('#project-list .an-lists-body').append($.templates('#project-row').render(data.data));
						// $('#project-list .an-lists-body > div:last-child').effect("highlight", {}, 3000);
						// $.get($('.my-projects').data('my-projects-url')).done(function(data) {
						// 	data = JSON.parse(data);
						// 	$('.my-projects .project-list').html(data.modal_content);
						// })

						if ($(e.target).attr('id') == 'create-project') {
							location.reload();
						}

						if ($(e.target).attr('id') == 'create-meeting') {
							$( $('#unscheduledSingle').render(data.data) )
							.appendTo('#unscheduled-meeting-body .an-lists-body')
							.effect('highlight', 500);
						}
					}
				}
			},
			complete: function() {
				$(e.target).find('button').prop('disabled', false);
			}
		});
	});

	$('[data-toggle="popover]').data("bs.popover", {inState: { click: false, hover: false, focus: false }});
});

$('#homework').click(function(e) {
	e.preventDefault();
	$(this).popover({
		html: true, 
		content: function() {
			return $('#homework-popover').html();
		}
	}).popover('show');

	$('[data-toggle="popover"]').not(this).popover('hide');
})

$('#open-rate').click(function(e) {
	e.preventDefault();
	$(this).popover({
		html: true, 
		content: function() {
			return $('#popover-rate').html();
		}
	}).popover('show');

	$('[data-toggle="popover"]').not(this).popover('hide');
})

$(document).on('click', '.btn-time + ul > li > a', function(e) {
	e.preventDefault();
	var time = parseFloat( $(this).parent().parent().data('minute') );
	var parent = $(this).parents('.time-wrapper');

	switch ($(this).data('option')) {
		case 'minute':
			$(parent).find('.btn-time > .number').text(Math.round(time * 100) / 100);
			break;
		case 'hour':
			$(parent).find('.btn-time > .number').text(Math.round(time / 60 * 100) / 100);
			break;
		case 'day':
			$(parent).find('.btn-time > .number').text(Math.round(time / 60 / 24 * 10) / 10);
	}

	$(parent).find('.btn-time > .text').text($(this).text());
})