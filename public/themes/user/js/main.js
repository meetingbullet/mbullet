$(document).ready(function() {
	// Show Template::set_message as Notification
	if ($('#notify').length > 0) {
		$.notify({
			message: $('#notify').html()
		}, {
			type: $('#notify').data('notify-type') ? ($('#notify').data('notify-type') == 'error' ? 'danger' : $('#notify').data('notify-type')) : 'info',
			z_index: 1051
		});
	}
})

/*
	Create and open a modal by data from element's attribute
*/
$(document).on('click.mb', '.mb-open-modal', (e) => {
	e.preventDefault();
	var modal_id = '#' + $(e.target).data('modal-id');
	var template = '\
	<div class="modal fade" id="'+ $(e.target).data('modal-id') +'" tabindex="-1" role="dialog">\
		<div class="modal-dialog modal-lg" role="document">\
			<div class="modal-content">\
			</div>\
		</div>\
	</div>';

	$.get($(e.target).data('url'), (data) => {
		data = JSON.parse(data);

		$('body').append(template);
		$(modal_id +' .modal-content').html(data.modal_content);
		$(modal_id).modal({backdrop: "static"});
	});

	$(document).on('hide.bs.modal', modal_id, function () {
		$(modal_id).remove();
	});
});
/*
	Backdrop z-index fix
	This solution uses a setTimeout because the .modal-backdrop isn't created 
	when the event show.bs.modal is triggered.

	http://stackoverflow.com/questions/19305821/multiple-modals-overlay
*/
$(document).on('show.bs.modal', '.modal', function () {
	var zIndex = 1040 + (10 * $('.modal:visible').length);
	$(this).css('z-index', zIndex);
	setTimeout(function() {
		$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
	}, 0);
});