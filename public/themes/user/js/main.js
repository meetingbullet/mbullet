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