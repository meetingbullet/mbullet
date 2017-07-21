// VietHD: DEBUGING
$('#test').click();

<?php if (has_permission('Project.Edit.All')): ?>
$('.mb-popover-project').on('shown.bs.popover', function() {
	$('.mb-editable').editable({
		success: function(data) {
			data = JSON.parse(data);

			$.mbNotify(data.message, data.message_type);
			
			if (data.message_type == 'danger') {
				return;
			}

			$(this).data('value', data.value);
			$(this).html(data.value);
		}
	});
})
<?php endif; ?>

$('.mb-popover-project').click(function(e){
	e.preventDefault();
})


$('.mb-popover-project.new').click(function(e){
	var that = this;

	$.get('<?php echo site_url('dashboard/mark_as_read/project/') ?>' + $(this).data('project-id'), (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			$(that).removeClass('new');

			// Remove remaining My Project [new]
			if ($('.badge-new').length == 2) {
				$('.badge-new').fadeOut('fast', function(){
					$(this).remove();
				});
			} else {
				$(that).find('.badge-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '#homework-content .child.new', function(e){
	var that = this;

	$.get('<?php echo site_url('dashboard/mark_as_read/homework/') ?>' + $(this).data('homework-id'), (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			var tr = 'tr[data-homework-id="'+ $(that).data('homework-id') +'"].child';
			$(tr).removeClass('new');

			// Remove remaining menu Homework [new] & My Todo new if all Rates has been read
			if ($('.badge-homework-new').length == 3) {
				$('.badge-homework-new').fadeOut('fast', function(){
					$(this).remove();
				});

				if ($('.badge-rate-new').length == 0) {
					$('.badge-todo-new').remove(); 
				}
			} else {
				$(tr + ' .badge-homework-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '#rate-content .child.new', function(e){
	var that = this;
	var type = $(this).data('mode');
	var object_id = $(this).data('id');
	var user_id = "";

	if (type == 'user') {
		object_id = $(this).data('meeting-id');
		user_id += "/" + $(this).data('id');
	}

	$.get("<?php echo site_url('dashboard/mark_as_read/') ?>"+ type + "/" + object_id + user_id, (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			var tr = 'tr[data-id="'+ $(that).data('id') +'"].child.' + type;
			$(tr).removeClass('new');

			// Remove remaining menu Homework [new] & My Todo new if all Rates has been read
			if ($('.badge-rate-new').length == 3) {
				$('.badge-rate-new').fadeOut('fast', function(){
					$(this).remove();
				});

				if ($('.badge-homework-new').length == 0) {
					$('.badge-todo-new').remove(); 
				}
			} else {
				$(tr + ' .badge-rate-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '.btn-confirm-homework', function() {
	var hw_id = $(this).data('homework-id');

	$.post("<?php echo site_url('homework/ajax_edit') ?>", {
		pk: hw_id,
		name: 'status',
		value: 'done'
	}, (data) => {
		data = JSON.parse(data);
		$.mbNotify(data.message, data.message_type);

		$(this)
		.parents('.child')
		.find('td')
		.wrapInner('<div style="display: block;" />')
		.parent()
		.find('td > div')
		.slideUp('fast', function(){
			$(this).parent().parent().remove();
		});

		$('#homework-popover tr.child[data-homework-id="'+ hw_id +'"]').remove();
		$('.homework-counter').text($('.homework-counter').text() - 1);
	})
})

// rating
$(document).on('click', '.todo-rating label', function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('input[type=radio]').removeAttr('checked');
	$(this).parent().find('input[type=radio]#' + input_id).attr('checked', '');
});

// evaluate
$(document).on("click", "#rate-content .submit", function(e) {
	e.preventDefault();
	var submit_btn = $(this);

	var todo_type = 'evaluate';

	var url = submit_btn.closest('.child').find('.data').data('url');

	var data = {};
	data.rate = submit_btn.closest('.child').find('.data').find('input[type=radio]:checked').val();

	if (typeof(data.rate) != 'undefined') {
		data.meeting_id = submit_btn.closest('.child').find('.data').data('id');

		if (submit_btn.closest('.child').hasClass('user')) {
			data.user_id = submit_btn.closest('.child').find('.data').data('id');
		}

		if (submit_btn.closest('.child').hasClass('agenda')) {
			data.agenda_id = submit_btn.closest('.child').find('.data').data('id');
		}

		if (submit_btn.closest('.child').hasClass('homework')) {
			data.homework_id = submit_btn.closest('.child').find('.data').data('id');
		}
	} else {
		var error = '<?php echo lang("db_rate_needed") ?>';
	}

	if (typeof(error) == 'undefined') {
		$.post({
			url: url,
			data: data,
		}).done(function(data) {console.log(data);
			data = JSON.parse(data);
			if (data.message_type == 'success') {
				$(submit_btn)
				.parents('.child')
				.find('td')
				.wrapInner('<div style="display: block;" />')
				.parent()
				.find('td > div')
				.slideUp('fast', function(){
					$(this).parent().parent().remove();
				});
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

});

<?php if ( ! $current_user->inited): ?>
$.mbOpenModalViaUrl('init', "<?php echo site_url('dashboard/init') ?>", 'modal-95');
<?php endif; ?>

// ------- baodg: start test ------- //
// setTimeout(function(){
// 	$.get({url : '<?php echo site_url('/test/init_project?data=') ?>' + JSON.stringify(INIT_DATA)}).done(function(data) {
// 		data = JSON.parse(data);
// 		console.log(data);

// 		$('#init .init-body .calendar').html(data.modal_content);
// 	});
// }, 3000);

$(document).on('click', '#init .init-body .init-project .action .delete-meeting', function() {
	var that = $(this);
	if ($('#init .init-body .calendar .init-project .action .delete-meeting').length <= 1) {
		swal("Warning", "We need at least 1 meeting to import", "warning");
		return;
	}

	var event_id = that.closest('tr').data('event-id');
	delete INIT_DATA.meetings[event_id];

	console.log(INIT_DATA);
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
	console.log(INIT_DATA);
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

					$.get({url : '<?php echo site_url('/test/init_project?data=') ?>' + JSON.stringify(INIT_DATA)}).done(function(refresh_data) {
						refresh_data = JSON.parse(refresh_data);
						console.log(refresh_data);

						$('#init .init-body .calendar').html(refresh_data.modal_content);
					});
				}
			}
		}
	});
});

$(document).on('click', '#init .init-footer.calendar #previous-step, #init .init-footer.calendar #next-step', function() {
	var screen_url = {
		'40': '<?php echo site_url('/test/init_project?data=') ?>',
		'50': '<?php echo site_url('/test/init_team?data=') ?>',
		'60': '<?php echo site_url('/test/init_finish?data=') ?>',
	};
	$('#init .init-body .config .content-container').fadeOut();
	$('#init .init-footer.calendar #previous-step, #init .init-footer.calendar button').attr('disabled', 'disabled');

	var that = $(this);

	if (that.attr('id') == 'next-step' && INIT_DATA.currentStep < 60) {
		INIT_DATA.currentStep += 10;
	}

	if (that.attr('id') == 'previous-step') {
		INIT_DATA.currentStep -= 10;
	}

	if (INIT_DATA.currentStep == 50 && INIT_DATA.path == 'owner') {
		$('#init #attachment-form').html();
		for (var event_id in INIT_DATA.meetings) {console.log('meeting');
			for (var i = 0; i < INIT_DATA.meetings[event_id].agenda.length; i++) {console.log('agenda');
				for (var j = 0; j < INIT_DATA.meetings[event_id].agenda[i].attachment.length; j++) {console.log('attachment');
					console.log(INIT_DATA.meetings[event_id].agenda[i].attachment[j]);
					$('#init #attachment-form').append(INIT_DATA.meetings[event_id].agenda[i].attachment[j]);
				}
			}
		}
	}

	if (INIT_DATA.currentStep == 60) {
		$('#init .init-footer.calendar #next-step').text('Import');

		var formData = $('#init .attachment-form').serialize();

		// You should sterilise the file names
		$.each(data.files, function(key, value)
		{
			formData = formData + '&filenames[]=' + value;
		});

		$.post({
			url: '<?php echo site_url('/test/upload') ?>',
			data: formData
		});
	}

	var url = screen_url[INIT_DATA.currentStep.toString()] + JSON.stringify(INIT_DATA);

	$.get({url}).done(function(data) {
		data = JSON.parse(data);
		console.log(data);

		$('#init .init-body .config .content-container .config-content').html(data.modal_content);
		$('#init .init-footer.calendar #previous-step, #init .init-footer.calendar button').removeAttr('disabled');
		$('#init .init-body .config .content-container').fadeIn();
	}).fail(function() {
		console.log('failed');
	});
});
// ------- baodg: end test ------- //