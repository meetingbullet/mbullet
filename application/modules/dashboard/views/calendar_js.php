$(document).ready(function() {
	$('#calendar').fullCalendar({
		customButtons: {
			ggcToggle: {
				text: 'Regular event',
				click: function() {
					var that = $(this);
					that.toggleClass('fc-state-active');

					if (typeof($('#calendar').fullCalendar('getEventSourceById', 'ggc')) == 'undefined') {
						$('#calendar').fullCalendar('addEventSource', {
							id: 'ggc',
							url: '<?php echo site_url('meeting/get_events/ggc') ?>',
							color: '#999',
							textColor: 'white',
							className: 'ggc-event'
						});
					} else {
						$('#calendar').fullCalendar('removeEventSource', 'ggc');
					}

					$('#calendar').fullCalendar('refetchEvents');
				}
			},
			mbcToggle: {
				text: 'MB meeting',
				click: function() {
					var that = $(this);
					that.toggleClass('fc-state-active');

					if (typeof($('#calendar').fullCalendar('getEventSourceById', 'mbc')) == 'undefined') {
						$('#calendar').fullCalendar('addEventSource', {
							id: 'mbc',
							url: '<?php echo site_url('meeting/get_events/mbc') ?>',
							color: '#70c1b3',
							textColor: 'white',
							className: 'mbc-event'
						});
					} else {
						$('#calendar').fullCalendar('removeEventSource', 'mbc');
					}

					$('#calendar').fullCalendar('refetchEvents');
				}
			}
		},
		header: {
			center: 'prev,today,next',
			left: 'title',
			right: 'ggcToggle, mbcToggle, month, agendaWeek, agendaDay'
		},
		eventRender: function(event, element) {
			if (element.hasClass('ggc-event')) {
				event.editable = false;
				element.attr("data-modal-id", "meeting-preview-modal");
				element.addClass("mb-open-modal");

				element.click(function(e) {
					e.preventDefault();
					$.mbOpenModalViaUrl('event-import-modal', '<?php echo site_url('meeting/import') ?>' + '?calendarId=' + encodeURIComponent(event.calendarId) + '&eventId=' + encodeURIComponent(event.eventId) + '&start=' + encodeURIComponent(moment(event.start).format('YYYY-MM-DD HH:mm:ss')) + '&end=' + encodeURIComponent(moment(event.end).format('YYYY-MM-DD HH:mm:ss')), 'modal-md');
					return false;
				});
			} else {
				var now = new Date();
				if (event.start._d.getTime() <= now.getTime()) {
					event.editable = false;
				}
			}
		},
		loading: function (isLoading) {
			if (isLoading) {
				swal({
					title: 'Loading events...',
					allowEscapeKey: false,
					imageUrl: '<?php echo Template::theme_url('images/clock.svg') ?>',
					showConfirmButton: false
				});
			} else {
				swal.close();
				//Possibly call you feed loader to add the next feed in line
			}
		},
		navLinks: true,
		firstDay: 1, // Monday
		// aspectRatio: 1, // content Width-to-Height
		editable: false,
		height: ($('.an-page-content').outerHeight() - $('.an-page-content .an-header').outerHeight() - 78 - $('.an-page-content .heading-wrapper .db-h1').outerHeight() - 30 - 2 - $('.an-page-content .an-footer').outerHeight() - $('.alert-wrapper').outerHeight()), /* 2 = calendar table border,  30 = calendar-wrapper padding top + bottom, 78 = an-content-body paading top + bottom + alert-warpper */ 
		eventLimit: true, // allow "more" link when too many events
		eventSources: <?php echo json_encode($event_sources) ?>,
		editable: true,
		eventDrop: function(event, delta, revertFunc) {
			$.post(
				'<?php echo site_url('meeting/edit_calendar_event') ?>',
				{
					meeting_id: event.meeting_id,
					start: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
					end: moment(event.end).format('YYYY-MM-DD HH:mm:ss')
				}
			).done(function(data) {
				data = JSON.parse(data);
				if (data.status == 0) {
					revertFunc();
				}
			}).fail(function() {
				revertFunc();
			})
		},
		eventResize: function(event, delta, revertFunc) {
			$.post(
				'<?php echo site_url('meeting/edit_calendar_event') ?>',
				{
					meeting_id: event.meeting_id,
					start: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
					end: moment(event.end).format('YYYY-MM-DD HH:mm:ss')
				}
			).done(function(data) {
				data = JSON.parse(data);
				if (data.status == 0) {
					revertFunc();
				}
			}).fail(function() {
				revertFunc();
			})
		},
		selectable: true,
		select: function(start, end, jsEvent, view) {
			var now = new Date();
			if (start._d.getTime() <= now.getTime()) {
				$('#calendar').fullCalendar('unselect');

				$.mbNotify('<?php echo lang("db_select_from_today_to_future") ?>', 'warning');
			} else {
				if ($('#calendar').fullCalendar('getView').type == 'month') {
					if (moment(end).unix() - moment(start).unix() >= 172800) {
						$.mbOpenModalViaUrl('calendar-create-event-modal', '<?php echo site_url('meeting/select_project') ?>' + '?start=' + encodeURIComponent(moment(start).format('YYYY-MM-DD HH:mm:ss')) + '&end=' + encodeURIComponent(moment(end).format('YYYY-MM-DD HH:mm:ss')), 'modal-sm');
					} else {
						$.mbOpenModalViaUrl('calendar-create-event-modal', '<?php echo site_url('meeting/select_project') ?>' + '?start=' + encodeURIComponent(moment(start).format('YYYY-MM-DD')) + ' 09:00:00' + '&end=' + encodeURIComponent(moment(start).format('YYYY-MM-DD')) + ' 10:00:00', 'modal-sm');
					}
				} else {
					$.mbOpenModalViaUrl('calendar-create-event-modal', '<?php echo site_url('meeting/select_project') ?>' + '?start=' + encodeURIComponent(moment(start).format('YYYY-MM-DD HH:mm:ss')) + '&end=' + encodeURIComponent(moment(end).format('YYYY-MM-DD HH:mm:ss')), 'modal-sm');
				}
			}
		},
		defaultView: 'agendaWeek',
	});

	$('#calendar .fc-ggcToggle-button, #calendar .fc-mbcToggle-button').addClass('fc-state-active');

	// import modal handler
	$(document).on('click', '#event-import-modal .dismiss-user', function() {
		var that = $(this);

		// if ($('#event-import-modal .email').length > 2 && ((that.parent().find('.owner').length && $('.in-system').length > 1) || (! that.parent().find('.owner').length && $('.in-system .owner').length > 0))) {
		if ($('#event-import-modal .email').length > 1 && ((that.parent().find('.owner').length && $('.in-system').length > 1) || (! that.parent().find('.owner').length && $('.in-system .owner').length > 0))) {
			var user_emails = $('input[name=user_emails]').val();
			var owner_email = $('input[name=owner_email]').val();

			if (user_emails == '') {
				user_emails = [];
			} else {
				user_emails = user_emails.split(',');
			}

			if (that.parent().find('.owner').length) {
				owner_email = '';
			} else {
				var removed_email = that.parent().find('.email').text();
				var index = user_emails.indexOf(removed_email);
				user_emails.splice(index, 1);
			}


			$('input[name=owner_email]').val(owner_email);
			$('input[name=user_emails]').val(user_emails.join(','));
			that.closest('.item').remove();

			if (that.parent().find('.owner').length) {
				$('#event-import-modal .in-system .email').first().click();
			} else {
				console.log(user_emails, owner_email);
			}
		} else {
			// swal("Warning", "A meeting needs at least 1 owner and 1 member", "warning");
			swal("Warning", "A meeting needs at least 1 owner", "warning");
		}
	});

	$(document).on('click', '#event-import-modal .email', function() {
		var that = $(this);

		if (that.closest('.item').hasClass('in-system')) {
			var user_emails = $('input[name=user_emails]').val();
			if (user_emails == '') {
				user_emails = [];
			} else {
				user_emails = user_emails.split(',');
			}

			owner_email = $('input[name=owner_email]').val();

			$('#event-import-modal .modal-body .in-system').each(function() {
				if ($(this).find('.email').text() == owner_email) {
					user_emails.push(owner_email);
					$(this).find('p').find('.owner').remove();
				}
			});

			owner_email = that.text();
			var index = user_emails.indexOf(owner_email);
			user_emails.splice(index, 1);
			that.parent().append('&nbsp;<span class="owner">(Owner)</span>');

			console.log(user_emails, owner_email);
			$('input[name=owner_email]').val(owner_email);
			$('input[name=user_emails]').val(user_emails.join(','));
		} else {
			swal("Warning", "This email does not exist in the system and could not be set as meeting owner.", "warning");
		}
	})

	$(document).on('click', '#event-import-modal #save_step_2', function(e) {
		e.preventDefault();
		// if ($('.email').length > 2 && $('input[name=owner_email]').val() != '' && $('input[name=user_emails]').val() != '') {
		if ($('.email').length > 1 && $('input[name=owner_email]').val() != '') {
			ajax_submit(true);
		} else {
			// swal("Warning", "A meeting needs at least 1 owner and 1 member", "warning");
			swal("Warning", "A meeting needs at least 1 owner", "warning");
		}
	});

	$(document).on('click', '#import-mode-modal .button-wrapper button', function(e) {
		if ($(this).attr('id') == 'convert-all') {
			$('.form-ajax input[name=import_mode]').val(1);
		} else {
			$('.form-ajax input[name=import_mode]').val(0);
		}
		$('#import-mode-modal').modal('hide');
		$('#event-import-modal #save_step_1').attr('disabled', '')
		ajax_submit(false);
	});

	$(document).on('click', '#event-import-modal #save_step_1', function(e) {
		e.preventDefault();
		var is_recurring = $('#event-import-modal #save_step_1').data('is-recurring');
		if (is_recurring == 0) {
			$('#event-import-modal #save_step_1').attr('disabled', '')
			ajax_submit(false);
		}
	});

	function ajax_submit(final_step) {
		var data = $('.form-ajax').serialize();
		// Since serialize does not include form's action button, 
		// we need to add it on our own.
		if (final_step) {
			swal({
				title: "Importing...",
				text: "Do not close the window until the process is done!",
				type: "warning",
				showCancelButton: false,
				showConfirmButton: false
			});
		}

		data += '&' + $('.form-ajax').find('[type="submit"]').attr('name') + '=';

		$.ajax({
			type: "GET",
			url: $('.form-ajax').attr('action'),
			data: data,
			error: () => {
				if (final_step) {
					$('.modal').modal('hide');
					swal.close();
					$.mbNotify('<?php echo lang('st_wrong_provided_data') ?>', 'danger');
				}
			},
			success: (data) => {
				try {
					data = JSON.parse(data);

					if (data.close_modal === 0) {
						$('.modal#event-import-modal .modal-content').html(data.modal_content);
					} else {
						$('.modal#event-import-modal').modal('hide');
					}

					if (data.message_type) {
						if (final_step && data.message_type == 'success') {
							$('#calendar').fullCalendar('refetchEvents');
						}
						swal.close();
						$.mbNotify(data.message, data.message_type);
					}
				} catch(err) {
					$('.modal').modal('hide');
					swal.close();
					$.mbNotify('<?php echo lang('st_wrong_provided_data') ?>', 'danger');
				}
			}
		});
	}

	$(document).on('click', '#calendar-create-event-modal #choose-project', function() {
		if ($('#calendar-create-event-modal select option:selected').length) {
			var project_key = $('#calendar-create-event-modal select option:selected').attr('value');
		} else {
			var project_key = $('#calendar-create-event-modal select option:first-child').attr('value');
		}

		var scheduled_start_time = $('#calendar-create-event-modal input[name="scheduled_start_time"]').val();
		var duration = $('#calendar-create-event-modal input[name="in"]').val();
		var url = '<?php echo site_url('meeting/') ?>';

		if (typeof(project_key) != 'undefined' && project_key != '') {
			url += 'create/' + project_key;
		} else {
			url+= 'create_private/';
		}

		$.get(url + '?recurring=1&scheduled_start_time=' + encodeURIComponent(scheduled_start_time) + '&in=' + encodeURIComponent(duration), (data) => {
			data = JSON.parse(data);

			if (data.message_type != 'success' && data.message_type != null) {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				return;
			}
			$('#calendar-create-event-modal .modal-content').fadeOut(400, function() {
				$('#calendar-create-event-modal .modal-dialog').removeClass('modal-sm').addClass('modal-lg');
				$('#calendar-create-event-modal .modal-content').html(data.modal_content);
				$('#calendar-create-event-modal .modal-content').fadeIn();
			});
		});
	});


	$(document).on('click', '#calendar-create-event-modal .form-ajax [type="submit"]', function(e) {
		e.preventDefault();
		$('#calendar-create-event-modal .form-ajax [type="submit"]').attr('disabled', 'disabled');
		var data = $('#calendar-create-event-modal .form-ajax').serialize();
		// Since serialize does not include form's action button, 
		// we need to add it on our own.

		data += '&' + $('#calendar-create-event-modal .form-ajax').find('[type="submit"]').attr('name') + '=';

		$.ajax({
			type: "POST",
			url: $('#calendar-create-event-modal .form-ajax').attr('action'),
			data: data,
			success: (data) => {
				data = JSON.parse(data);

				if (data.close_modal === 0) {
					$('.modal#calendar-create-event-modal .modal-content').html(data.modal_content);
				} else {
					$('.modal#calendar-create-event-modal').modal('hide');
				}

				if (data.message_type) {
					if (data.message_type == 'success') {
						$('#calendar').fullCalendar('refetchEventSources', ['mbc', 'mbcp']);
						$("textarea[name=recurring]").val('');
					}
					$.mbNotify(data.message, data.message_type);
				}

				$('#calendar-create-event-modal .form-ajax [type="submit"]').removeAttr('disabled');
			}
		});
	});

	$(document).on('hide.bs.modal', '#calendar-create-event-modal', function() {
		console.log('hide');
		if ($('.rimain button[name="ridelete"]').attr('display') != 'none') {
			$('.rimain button[name="ridelete"]').click();
		}
	});

	$("textarea[name=recurring]").recurrenceinput({
		formOverlay: {
			speed: 'slow',
			fixed: false
		},
		rtemplate: {
			daily: {
				rrule: 'FREQ=DAILY',
				fields: [
					'ridailyinterval',
					'rirangeoptions'
				]
			},
			weekly: {
				rrule: 'FREQ=WEEKLY',
				fields: [
					'riweeklyinterval',
					'riweeklyweekdays',
					'rirangeoptions'
				]
			},
			monthly: {
				rrule: 'FREQ=MONTHLY',
				fields: [
					'rimonthlyinterval',
					'rimonthlyoptions',
					'rirangeoptions'
				]
			},
			yearly: {
				rrule: 'FREQ=YEARLY',
				fields: [
					'riyearlyinterval',
					'riyearlyoptions',
					'rirangeoptions'
				]
			}
		},
		hasRepeatForeverButton: false
	});

	$(document).on('click', '.ributtons .risavebutton', function() {
		if ($('#messagearea').text() == '' || $('#messagearea').css('display') == 'none') {
			$('#calendar-create-event-modal').fadeToggle();
		}
	});

	$(document).on('click', '.ributtons .ricancelbutton', function() {
		$('#calendar-create-event-modal').fadeIn();
		if ($('#calendar-create-event-modal textarea[name=rrule_recurring]').val() == '') {
			$('#calendar-create-event-modal input[name="repeat"]').removeAttr('checked');
			$('#calendar-create-event-modal input[name="repeat"]').prop("checked", false);
		}
	});

	$(document).on('click', '#calendar-create-event-modal input[name="repeat"]', function() {
		var that = $(this);

		if (that.prop("checked") == true) {
			$('#calendar-create-event-modal').fadeOut();
			$('.rimain button[name="riedit"]').click();
		} else {
			$('#calendar-create-event-modal textarea[name=rrule_recurring]').val('');
			$('#calendar-create-event-modal input[name=readable]').val('Does not repeat');
			$('#calendar-create-event-modal #readble').html('Does not repeat');
		}
	});

	$('textarea[name=recurring]').on('change', function() {
		var that = $(this);
		if ($('#calendar-create-event-modal input[name="repeat"]').prop("checked") == true) {
			$('#calendar-create-event-modal textarea[name=rrule_recurring]').val($('textarea[name=recurring]').val());
			$('#calendar-create-event-modal input[name=readable]').val($('.ridisplay .ridisplay').text());
			$('#calendar-create-event-modal #readble').html($('.ridisplay .ridisplay').text());
		} else {
			$('#calendar-create-event-modal textarea[name=rrule_recurring]').val('');
			$('#calendar-create-event-modal input[name=readable]').val('Does not repeat');
			$('#calendar-create-event-modal #readble').html('Does not repeat');
		}
	});

	// var $element = $(".main-wrapper");
	// var lastHeight = $(".main-wrapper").css('height');
	// function checkForChanges()
	// {
	// 	if ($element.css('height') != lastHeight)
	// 	{
	// 		console.log('body height changed');
	// 		$('#calendar').fullCalendar('option', 'height', ($('.an-page-content').outerHeight() - $('.an-page-content .an-header').outerHeight() - 78 - $('.an-page-content .heading-wrapper .db-h1').outerHeight() - 30 - 2 - $('.an-page-content .an-footer').outerHeight() - $('.alert-wrapper').outerHeight()));
	// 		/* 2 = calendar table border, 30 = calendar-wrapper padding top + bottom, 78 = an-content-body paading top + bottom + alert-warpper */
	// 		lastHeight = $element.css('height');
	// 	}

	// 	setTimeout(checkForChanges, 500);
	// }

	$('.invitations .alert').on('closed.bs.alert', function() {console.log('closed');
		$('#calendar').fullCalendar('option', 'height', ($('.an-page-content').outerHeight() - $('.an-page-content .an-header').outerHeight() - 78 - $('.an-page-content .heading-wrapper .db-h1').outerHeight() - 30 - 2 - $('.an-page-content .an-footer').outerHeight() - $('.alert-wrapper').outerHeight()));
		/* 2 = calendar table border, 30 = calendar-wrapper padding top + bottom, 78 = an-content-body paading top + bottom + alert-warpper */
	});

	$('.an-sidebar-nav.js-sidebar-toggle-with-click *').on('hidden.bs.collapse, shown.bs.collapse', function() {console.log('toggled');
		$('#calendar').fullCalendar('option', 'height', ($('.an-page-content').outerHeight() - $('.an-page-content .an-header').outerHeight() - 78 - $('.an-page-content .heading-wrapper .db-h1').outerHeight() - 30 - 2 - $('.an-page-content .an-footer').outerHeight() - $('.alert-wrapper').outerHeight()));
		/* 2 = calendar table border, 30 = calendar-wrapper padding top + bottom, 78 = an-content-body paading top + bottom + alert-warpper */
	});

	/**
	 * Resize end event
	 * https://stackoverflow.com/questions/5489946/jquery-how-to-wait-for-the-end-of-resize-event-and-only-then-perform-an-ac
	 */

	var rtime;
	var timeout = false;
	var delta = 200;

	$(window).resize(function() {
		rtime = new Date(-1E12);
		if (timeout === false) {
			timeout = true;
			setTimeout(resizeend, delta);
		}
	});

	function resizeend() {
		if (new Date() - rtime < delta) {
			setTimeout(resizeend, delta);
		} else {
			timeout = false;
			console.log('Done resizing');
			$('#calendar').fullCalendar('option', 'height', ($('.an-page-content').outerHeight() - $('.an-page-content .an-header').outerHeight() - 78 - $('.an-page-content .heading-wrapper .db-h1').outerHeight() - 30 - 2 - $('.an-page-content .an-footer').outerHeight() - $('.alert-wrapper').outerHeight()));
			/* 2 = calendar table border, 30 = calendar-wrapper padding top + bottom, 78 = an-content-body paading top + bottom + alert-warpper */
		}
	}
});
