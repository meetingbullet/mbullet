Selectize.define('select-member', function(options) {
	var self = this;

	// Override updatePlaceholder method to keep the placeholder
	this.updatePlaceholder = (function() {
		var original = self.updatePlaceholder;
		return function() {
			// do your logic
			return false;
			// return original.apply(this, arguments);
		};
	})();
});

// Add step
$('#add-step').click((e) => {
	e.preventDefault();

	$.get('<?php e(site_url('step/create/' . $action_key)) ?>', (data) => {
		data = JSON.parse(data);
		console.log(data.modal_content);
		$('.modal .modal-content').html(data.modal_content);
		$('.modal').modal();
	});

});

// Set form-ajax to work inside a modal
$(document).on("submit", '.form-ajax', (e) => {
	e.preventDefault();

	var method = $(e.target).attr('method') ? $(e.target).attr('method') : 'post';
	var data = $(e.target).serialize();

	// Since serialize does not include form's action button, 
	// we need to add it on our own.
	data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

	$.ajax({
		type: "POST",
		url: $(e.target).attr('action'),
		data: data,
		success: (data) => {
			data = JSON.parse(data);

			if (data.close_modal === 0) {
				$('.modal .modal-content').html(data.modal_content);
				$('.modal').modal('show');
			} else {
				$('.modal').modal('hide');
			}

			if (data.message_type) {
				$.notify({
					message: data.message
				}, {
					type: data.message_type,
					z_index: 1051
				});

				if (data.message_type == 'success') {
					// @TODO Refresh Step list
				}
			}
		}
	});
});