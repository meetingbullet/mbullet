$(document).ready(function() {
	$('a#forgot_password').click(function() {
		$.post({
			url: '<?php echo site_url('users/forgot_password') ?>',
			data: {
				email: '<?php echo $email ?>',
				send: 'true'
			}
		}).done(function(data) {
			data = JSON.parse(data);console.log(data);
			$.notify({
				message: data.message
			}, {
				type: data.status == 1 ? 'success' : 'danger',
				z_index: 1051
			});
		});
	});
});