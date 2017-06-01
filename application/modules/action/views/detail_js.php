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

// Add meeting
$('#add-meeting').click((e) => {
	e.preventDefault();

	$.get('<?php e(site_url('meeting/create/' . $action_key)) ?>', (data) => {
		data = JSON.parse(data);
		$('.modal .modal-content').html(data.modal_content);
		$('.modal').modal({backdrop: "static"});
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
					// Meeting created
					if (data.data.meeting_key) {
						$('#meeting-list tbody').append($.templates('#meeting-row').render(data.data));
					}

					// Action edited
					if ($(e.target).attr('id') == 'form-save-action') {
						// Gather form data
						var name = $('#form-save-action input[name="name"]').val();
						var lang_success = $('#form-save-action select[name="success_condition"] option:selected').text();
						var lang_type = $('#form-save-action select[name="action_type"] option:selected').text();
						var owner_id = $('#form-save-action input[name="owner_id"]').val();
						var owner_html = $('.action-detail .owner').html();
						var point_value = $('#form-save-action input[name="point_value"]').val();
						var team = $('#form-save-action input[name="team"]').val().split(',');
						var resource_html = '';

						project_members.forEach((item) => {
							if (item.id == owner_id) {
								owner_html = '<img class="user-avatar" title="'+ item.name +'" src="'+ item.avatar +'" style="width: 24px; height: 24px"> <span class="user-name">'+ item.name +'</span>';
							}

							if (team.indexOf(item.id) >= 0) {
								resource_html += '<li>\
													<img class="user-avatar" title="'+ item.name + '" src="'+ item.avatar + '" style="width: 24px; height: 24px">\
													<span class="user-name">'+ item.name + '</span>\
													<span class="badge badge-'+ item.cost_of_time + ' badge-bordered pull-right">'+ item.cost_of_time_name +'</span>\
												</li>';
							}
						});

						// Update view
						$('#action-name').text(name);
						$('.action-detail .success').text(lang_success);
						$('.action-detail .type').text(lang_type);
						$('.action-detail .point-value').text(point_value);
						$('.action-detail .owner').html(owner_html);
						$('#action-resource').html(resource_html);
					}
				}
			}
		}
	});
});