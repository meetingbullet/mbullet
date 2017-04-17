$(document).ready(function() {
    // Show Template::set_message as Notification
    if ($('#notify').length > 0) {
        $.notify({
            message: $('#notify').html()
        }, {
            type: $('#notify').data('notify-type') ? $('#notify').data('notify-type') : 'info',
            z_index: 1051
        });
    }
})