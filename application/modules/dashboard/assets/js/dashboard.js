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
                    $.notify({
                        message: data.message
                    }, {
                        type: data.message_type,
                        z_index: 1051
                    });

                    if (data.message_type == 'success') {
                        // @TODO Refresh Step list
                        setTimeout(function() {
                            location.reload();
                        }, 700);
                    }
                }
            }
        });
    });
});