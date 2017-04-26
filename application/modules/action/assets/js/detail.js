$(document).ready(function() {
	$('#update-action').on('click', function() {
		var url = $('#update-action').data('update-action-url');
		$.get(url).done(function(data) {console.log(url);
			data = JSON.parse(data);
			$('#bigModal .modal-content').empty().append(data.modal_content);
		});
	});
});