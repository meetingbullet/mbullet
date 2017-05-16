$(document).ready(function() {
	$('a#resend_confirm_mail, button#resend_confirm_mail').click(function() {
		$.get('<?php echo site_url('users/resend_confirm_change_email') ?>')
		.done(function(data) {console.log(data);
			data = JSON.parse(data);
			$.notify({
				message: data.message
			}, {
				type: data.status == 1 ? 'success' : 'danger',
				z_index: 1051
			});
		});
	});
});