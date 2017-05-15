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
    $(document).on("submit", '.form-ajax', (e) => {
        e.preventDefault();

        var method = $(e.target).attr('method') ? $(e.target).attr('method') : 'post';
        var data = $(e.target).serialize();

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
                    $('#bigModal .modal-content').html(data.modal_content);
                    $('#bigModal').modal('show');
                } else {
                    $('#bigModal').modal('hide');
                }

                if (data.message_type) {
                    $.notify({
                        message: data.message
                    }, {
                        type: data.message_type,
                        z_index: 1051
                    });

					if (data.message_type == 'success') {
                        setTimeout(function() {
                            location.reload();
                        }, 700);
					}
                }
            }
        });
    });

	$('li.update-btn a').click(function() {
		var url = $(this).data('update-project-status-url');
		$.get(url).done(function(data) {console.log(data);
			if (data == 1) {
				location.reload();
			}
		});
	});

	$('li.update-btn a').click(function() {
		var url = $(this).data('update-project-status-url');
		$.get(url).done(function(data) {console.log(data);
			if (data == 1) {
				location.reload();
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