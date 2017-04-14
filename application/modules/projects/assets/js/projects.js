$(document).ready(function() {
	$('#board .status .actions .item').draggable({
		cursor: 'move',
		containment: '#board',
		// zIndex: 100,
		stack: '.item',
		start: function(event, ui) {
		}
	});
	$('#board .status .actions').droppable( {
		drop: function(event, ui) {
			ui.draggable.position({
				my: 'center',
				at: 'center',
				of: $(this),
				using: function(pos) {
					$(this).animate(pos, 'slow', 'linear');
				}
			});
		}
	});
});