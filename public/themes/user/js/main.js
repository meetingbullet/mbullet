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

	// Pull Modal inside main content to below Body in order to work with Blur Fx 
	var modal_html ="";
	$('.main-wrapper .modal').each(function(){
		modal_html += $(this)[0].outerHTML;
		$(this).remove();
	});
	$('body').append(modal_html);

	// Enable jQuery tooltip
	$('[data-toggle="tooltip"]').tooltip(); 
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

// Fix modal-open class remove when there are open modals
$(document).on('hidden.bs.modal', '.mb-modal', function (e) {
	$(this).remove();

	if ($('.modal.in').length > 0) {
		$('body').addClass('modal-open');
	}
});

$(document).on('hidden.bs.modal', '.modal', function (e) {
	if ($('.modal.in').length > 0) {
		$('body').addClass('modal-open');
	}
});


/*
	Create and open a modal by data from element's attribute
*/
$(document).on('click.mb', '.mb-open-modal', function(e) {
	e.preventDefault();
	var modal_id = $(this).data('modal-id') ? $(this).data('modal-id') : 'mb-modal-' + Math.round(Math.random() * 10e10).toString();
	var dialog_class = $(this).data('modal-dialog-class') ? $(this).data('modal-dialog-class') : 'modal-lg';
	var url = $(this).data('url') ? $(this).data('url') : $(this).attr('href');
	var content = $(this).data('content');
	var title = $(this).data('title');

	if (typeof url == 'undefined' || typeof url == 'null') {
		$.mbOpenModal(modal_id, title, content, dialog_class);
	} else {
		$.mbOpenModalViaUrl(modal_id, url, dialog_class);
	}
});

$.mbOpenModal = function(modal_id, title, content, dialog_class) {
	if (! $('#' + modal_id + '-placeholder').length) {
		$('body').append('<div id="' + modal_id + '-placeholder"></div>');

		var template = `
		<div class="modal fade mb-modal" id="${modal_id}" tabindex="-1" role="dialog">
			<div class="modal-dialog ${dialog_class}" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">${title}</h4>
					</div>
					<div class="modal-body">
						${content}
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>`;

		modal_id = '#' + modal_id;

		$('body').append(template);
		$(modal_id).modal({backdrop: "static"});
		$(modal_id + '-placeholder').remove();
	}
}

$.mbOpenModalViaUrl = function(modal_id, url, dialog_class = 'modal-lg') {
	if (! $('#' + modal_id + '-placeholder').length) {
		$('body').append('<div id="' + modal_id + '-placeholder"></div>');

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

			if (data.message_type != 'success' && data.message_type != null) {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				return;
			}

			$('body').append(template);
			$(modal_id +' .modal-content').html(data.modal_content);
			$(modal_id).modal({backdrop: "static"});
			$(modal_id + '-placeholder').remove();
		});
	}
}

$.mbNotify = function (message, message_type) {
	$.notify({
		message: message
	}, {
		type: message_type,
		z_index: 1051
	});
}

function decodeHtml(html) {
	var txt = document.createElement("textarea");
	txt.innerHTML = html;
	return txt.value;
}


/*
	sprintf() for JavaScript.
	Grabs from https://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format
	Usage: "{0} is dead, but {1} is alive! {0} {2}".format("ASP", "ASP.NET")
*/
if (!String.prototype.format) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/{(\d+)}/g, function(match, number) { 
		return typeof args[number] != 'undefined'
			? args[number]
			: match
		;
		});
	};
}