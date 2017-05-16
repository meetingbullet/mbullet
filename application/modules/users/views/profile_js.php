$(document).ready(function() {
	$('a#forgot_password').click(function() {
		$.get('<?php echo site_url('users/get_current_user_info') ?>')
		.done(function(user_data) {
			user_data = JSON.parse(user_data);
			if (user_data.status == 1) {
				var email = user_data.data.email;
				$.post({
					url: '<?php echo site_url('users/forgot_password') ?>',
					data: {
						email: email,
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
			}
		});
	});
});