$(document).ready(function() {
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

		// Since serialize does not include form's action button, 
		// we need to add it on our own.
		data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

		var form = $(e.target);
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
				}

				if (data.message_type == 'success') {
					if ($(form).hasClass('edit-user-form')) {
						$.get(window.location.href).done(function(data) {
							data = JSON.parse(data);
							$('.tab-pane').html(data.modal_content);
						});
					}

					// New Role created, insert to list
					if ( $(this).attr('id') == 'form-create-role') {
						$('#role-list .an-lists-body').append($.templates('#rolls-royce').render(data.data));

						if (data.data.join_default == 1) {
							$('.list-join-default').html('');
							$('#role-list .an-lists-body > div:last-child .list-join-default').html('<i class="ion-checkmark-circled"></i>');
						}

						$('#role-list .an-lists-body > div:last-child').effect("highlight", {}, 3000);
					} else if ( $(this).attr('id') == 'form-update-role') {
						// Role edited

						if (data.data.join_default == 1) {
							$('.list-join-default').html('');
							$('#role-' + data.data.role_id + ' .list-join-default').html('<i class="ion-checkmark-circled"></i>');
						}

						$('#role-' + data.data.role_id + ' .list-name a').text(data.data.name);
						$('#role-' + data.data.role_id + ' .list-description').text(data.data.description);
						$('#role-' + data.data.role_id).effect("highlight", {}, 3000);
					}
				}
			}
		});
	});
});

$('#toggle_dropdown').click((e) => {
	e.preventDefault();
	$('#toggle_dropdown').closest('li').toggleClass('open');
});

$(document).on('click', function(e) {
	if ($(e.target).is('#toggle_dropdown') === false) {
		$('#toggle_dropdown').closest('li').removeClass('open');
	}
});
