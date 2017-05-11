$(document).ready(function() {
	$('#change-step-status').click(function() {
		var url = $(this).data('update-status-url');
		var status = $(this).data('next-status');
		$.get(url).done(function(data) {
			location.reload();
		});
	})

	$('#add-task').click(function() {
		var url = $(this).data('add-task-url');
		$.get(url).done(function(data) {
			data = JSON.parse(data);
			$('#bigModal .modal-content').html(data.modal_content);
		});
	});

	$('#test').click(function() {
		var url = $('#step-name').data('evaluator-url');
		$.get(url).done(function(data) {
			data = JSON.parse(data);
			$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
			$('.modal-monitor-evaluator').modal({
				backdrop: 'static'
			});
		});
	});
});