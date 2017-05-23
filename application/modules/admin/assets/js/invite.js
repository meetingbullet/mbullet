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
        console.log(data);

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
                    $.notify({
                        message: data.message
                    }, {
                        type: data.message_type,
                        z_index: 1051
                    });
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