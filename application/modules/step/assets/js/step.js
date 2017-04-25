$(document).ready(function() {
	$('#change-step-status').click(function() {
		var url = $(this).data('update-status-url');
		var status = $(this).data('next-status');
		$.get(url).done(function(data) {
			data = JSON.parse(data);
			if (data.message_type) {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				if (data.message_type == 'success') {
					$('#change-step-status').html(`<i class="${data.data.icon}"></i>&nbsp;${data.data.label}`).data('update-status-url', data.data.url).data('next-status', data.data.next_status);
					$('#status').text(status.replace(/-/g, ' '));
				}
			}
		});
	})
});