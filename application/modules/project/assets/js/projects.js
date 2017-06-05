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

	// Set form-ajax to work inside a modal
	$(document).on("submit", '.form-ajax', function(e) {
		e.preventDefault();

		var method = $(this).attr('method') ? $(this).attr('method') : 'post';
		var data = $(this).serialize();

		// Since serialize does not include form's action button, 
		// we need to add it on our own.
		data += '&' + $(this).find('[type="submit"]').attr('name') + '=';

		$.ajax({
			type: "POST",
			url: $(this).attr('action'),
			data: data,
			success: (data) => {
				data = JSON.parse(data);

				if (data.close_modal === 0) {
					$('.modal .modal-content').html(data.modal_content);
				} else {
					$('.modal').modal('hide');
				}

				if (data.message_type) {
					$.notify({
						message: data.message
					}, {
						type: data.message_type,
						z_index: 1051
					});

					if (data.message_type == 'success') {
						// New meeting created, insert to table

						if ($(this).attr('id') == 'create-meeting') {
							$('#no-meeting').remove();
							$('#meeting-list .an-lists-body').append($.templates('#meeting-row').render(data.data));
							$('#meeting-list .an-lists-body > div:last-child').effect("highlight", {}, 3000);
						}
					}
				}
			}
		});
	});

	$('li.update-btn a').click(function() {
		if ($(this).hasClass('disabled')) {
			return;
		}

		var _this = this;
		var url = $(this).data('update-project-status-url');
		$.get(url).done(function(data) {
			data = JSON.parse(data);

			if (data.message_type) {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				// Status updated
				if (data.message_type == 'success') {
					$('#project-status').attr('class', 'msg-tag label label-bordered label-' + data.status);
					$('#project-status').text(data.lang_status);

					$('li.update-btn a').removeClass('disabled');
					$(_this).addClass('disabled');
				}
			}
		});
	});

	$('li.update-info-btn a').click(function() {
		var url = $(this).data('update-project-url');
		$.get(url).done(function(data) {
			data = JSON.parse(data);console.log(data);
			$('#bigModal .modal-content').html(data.modal_content)
		});
	})
});