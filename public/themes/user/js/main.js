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
$(document).on('click.mb', '.mb-open-modal', function(e) {
	e.preventDefault();
	var modal_id = $(this).data('modal-id');
	var dialog_class = $(this).data('modal-dialog-class') ? $(this).data('modal-dialog-class') : 'modal-lg';
	var url = $(this).data('url');

	$.mbOpenModal(modal_id, url, dialog_class);
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

$(document).on('hidden.bs.modal', '.modal', function () {
	// Fix modal-open class remove when there are open modals
	if ($('.modal.in').length > 0) {
		$('body').addClass('modal-open');
	}
});

$.mbOpenModal = function(modal_id, url, dialog_class = 'modal-lg') {

	var template = '\
	<div class="modal fade mb-modal" id="'+ modal_id +'" tabindex="-1" role="dialog">\
		<div class="modal-dialog '+ dialog_class +'" role="document">\
			<div class="modal-content">\
			</div>\
		</div>\
	</div>';

	modal_id = '#' + modal_id;

	$.get(url, (data) => {
		data = JSON.parse(data);

		$('body').append(template);
		$(modal_id +' .modal-content').html(data.modal_content);
		$(modal_id).modal({backdrop: "static"});
	});
}