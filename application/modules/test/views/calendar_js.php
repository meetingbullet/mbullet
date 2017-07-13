$(document).ready(function() {
	$('#calendar').fullCalendar({
		customButtons: {
			ggcToggle: {
				text: 'Google Calendar',
				click: function() {
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
				text: 'MB Calendar',
				click: function() {
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
			right: 'ggcToggle, mbcToggle, month, basicWeek, basicDay'
		},
		eventRender: function(event, element) {
			if (element.hasClass('ggc-event')) {
				element.attr("data-modal-id", "meeting-preview-modal");
				element.addClass("mb-open-modal");

				element.click(function(e) {
					e.preventDefault();
					$.mbOpenModalViaUrl('event-import-modal', '<?php echo site_url('meeting/import') ?>' + '?calendarId=' + encodeURIComponent(event.calendarId) + '&eventId=' + encodeURIComponent(event.eventId) + '&start=' + encodeURIComponent(moment(event.start).format('YYYY-MM-DD HH:mm:ss')) + '&end=' + encodeURIComponent(moment(event.end).format('YYYY-MM-DD HH:mm:ss')), 'modal-md');
					return false;
				});
			}
		},
		navLinks: true,
		firstDay: 1, // Monday
		aspectRatio: 1, // content Width-to-Height
		editable: false,
		eventLimit: true, // allow "more" link when too many events
		eventSources: <?php echo json_encode($event_sources) ?>
	});

	// import modal handler
	$(document).on('click', '#event-import-modal .dismiss-user', function() {
		var that = $(this);

		if ($('.email').length > 2 && ((that.parent().find('.owner').length && $('.in-system').length > 1) || (! that.parent().find('.owner').length && $('.in-system .owner').length > 0))) {
			user_emails = $('input[name=user_emails]').val();
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
			swal("Warning", "A meeting needs at least 1 owner and 1 member", "warning");
		}
	});

	$(document).on('click', '#event-import-modal .email', function() {
		var that = $(this);

		if (that.closest('.item').hasClass('in-system')) {
			user_emails = $('input[name=user_emails]').val();
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
		if ($('.email').length > 2 && $('input[name=owner_email]').val() != '' && $('input[name=user_emails]').val() != '') {
			ajax_submit(true);
		} else {
			swal("Warning", "A meeting needs at least 1 owner and 1 member", "warning");
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
});