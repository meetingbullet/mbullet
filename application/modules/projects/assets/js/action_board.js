$(document).ready(function() {
	$('#board .status .actions').sortable({
		connectWith: '#board .status .actions',
		receive: function (e, ui) {
			console.log('received');
		}
	});

	refresh_action_board();
});

function refresh_action_board() {
	var url = $('#board').data('url')
	setInterval(
		function() {
			$('#board #loading').fadeIn();
			$.get(url).done(function(data) {
				$('#board #loading').fadeOut();
				$('#board .status .actions').empty();

				data = JSON.parse(data);
				data.open.forEach(function(item, index) {
					var div = `<div class="item">
									${item.action_key}
								</div>`;
					$('#board #status_open .actions').append(div);
				});
				data.inprogress.forEach(function(item, index) {
					var div = `<div class="item">
									${item.action_key}
								</div>`;
					$('#board #status_inprogress .actions').append(div);
				});
				data.ready.forEach(function(item, index) {
					var div = `<div class="item">
									${item.action_key}
								</div>`;
					$('#board #status_ready .actions').append(div);
				});
				data.resolved.forEach(function(item, index) {
					var div = `<div class="item">
									${item.action_key}
								</div>`;
					$('#board #status_resolved .actions').append(div);
				});
			});
		}, 60000
	);
}