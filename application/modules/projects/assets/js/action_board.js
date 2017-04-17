$(document).ready(function() {
	$('#board .status .actions').sortable({
		connectWith: '#board .status .actions',
		items: '.item',
		// update: function (e, ui) {
		// 	if (this === ui.item.parent()[0]) {
		// 		console.log(ui.item.index())
		// 		var url = $('#board').data('drag-drop-url');
		// 		$.get(url,{
		// 			status_order: ui.item.index(),
		// 			action_id: ui.item.data('action-id'),
		// 			status: ui.item.parent().parent().attr('id')
		// 		}).done(function(data) {
		// 			data = JSON.parse(data);
		// 			if (data.status == 0) {
		// 				console.log('update failed');
		// 				refresh_action_board();
		// 			} else {
		// 				console.log('update success');
		// 			}
		// 		});
		// 	}
		// }
	});

	// refresh_action_board_multiple();
});

function refresh_action_board_multiple() {
	setInterval(function() {
			refresh_action_board();
		}, 60000
	);
}

function refresh_action_board() {
	var url = $('#board').data('refresh-url');
	$('#board #loading').fadeIn();
	$.get(url).done(function(data) {
		$('#board #loading').fadeOut();
		$('#board .status .actions').empty();

		data = JSON.parse(data);
		data.open.forEach(function(item, index) {
			var div = `<div class="item" data-action-id="${item.action_id}">
							${item.action_key}
						</div>`;
			$('#board .status#open .actions').append(div);
		});
		data.inprogress.forEach(function(item, index) {
			var div = `<div class="item" data-action-id="${item.action_id}">
							${item.action_key}
						</div>`;
			$('#board .status#inprogress .actions').append(div);
		});
		data.ready.forEach(function(item, index) {
			var div = `<div class="item" data-action-id="${item.action_id}">
							${item.action_key}
						</div>`;
			$('#board .status#ready .actions').append(div);
		});
		data.resolved.forEach(function(item, index) {
			var div = `<div class="item" data-action-id="${item.action_id}">
							${item.action_key}
						</div>`;
			$('#board .status#resolved .actions').append(div);
		});
	});
}