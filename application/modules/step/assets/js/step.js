$(document).ready(function() {
	$('#change-step-status').click(function() {
		var url = $(this).data('update-status-url');
		var status = $(this).data('next-status');
		$.get(url).done(function(data) {
			location.reload();
		});
	})

	$('#add-agenda').click(function() {
		var url = $(this).data('add-agenda-url');
		$.get(url).done(function(data) {
			data = JSON.parse(data);
			$('#bigModal .modal-content').html(data.modal_content);
		});
	});
});