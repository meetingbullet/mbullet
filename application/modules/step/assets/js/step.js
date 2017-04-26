$(document).ready(function() {
	$('#change-step-status').click(function() {
		var url = $(this).data('update-status-url');
		var status = $(this).data('next-status');
		$.get(url).done(function(data) {
			location.reload();
		});
	})
});