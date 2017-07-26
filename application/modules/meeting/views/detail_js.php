// Enable jQuery tooltip
$('[data-toggle="tooltip"]').tooltip(); 

// Read more Notes & Goal
var rm_option = {
	speed: 300,
	moreLink: '<a class=\'readmore rm-more\' href="#"><?php e(lang('show_more'))?></a>',
	lessLink: '<a class=\'readmore rm-less\' href="#"><?php e(lang('show_less'))?></a>'
};

$('.detail-goal, .meeting-notes').readmore(rm_option);

// Edit meeting
$('#edit-meeting').click((e) => {
	e.preventDefault();

	$.get('<?php e(site_url('meeting/edit/' . $meeting_key)) ?>', (data) => {
		data = JSON.parse(data);
		$('.modal-edit .modal-content').html(data.modal_content);
		$('.modal-edit').modal({backdrop: "static"});
	});

});

$('#start-meeting').click((e) => {
	e.preventDefault();
	var _this = this;

	$.post('<?php e(site_url('meeting/update_status/' . $meeting_key)) ?>', {status: 'ready'}, (result) => {
		data = JSON.parse(result);
		
		if (data.message_type == 'success') {
			$('#start-meeting').addClass('hidden');
			$('#open-meeting-monitor').removeClass('hidden');
		}
	});
});

// Open meeting decider if there is a agenda without confirmed status
if ($('#meeting-status').data('is-owner') == '1' && ($('#meeting-status').data('status') == 'resolved' || $('#meeting-status').data('status') == 'finished')) {
	$('.table-detail-agenda tr').each((i, item) => {
		if ($(item).data('confirm-status') == '') {
			$('#meeting-decider-modal .modal-content').html('');

			$.get('<?php e(site_url('meeting/decider/' . $meeting_key)) ?>', (data) => {
				data = JSON.parse(data);

				if (data.modal_content == '') {
					$.mbNotify(data.message, data.message_type);
					return;
				}

				$('#meeting-decider-modal .modal-content').html(data.modal_content);
				$('#meeting-decider-modal').modal({backdrop: "static"});
			});
		}
	});
}

// Set form-ajax to work inside a modal
$(document).on("submit", '.form-ajax', (e) => {
	e.preventDefault();

	var method = $(e.target).attr('method') ? $(e.target).attr('method') : 'post';
	var data = $(e.target).serialize();

	// Since serialize does not include form's action button, 
	// we need to add it on our own.
	data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

	// Clear script in an opened modal for Javascript run after modal is updated
	$('.modal.in .modal-content script').text('');

	// Temporary disable form's buttons to prevent duplicate requests
	$(this).find('button').prop('disabled', true);

	$.ajax({
		type: "POST",
		url: $(e.target).attr('action'),
		data: data,
		complete: function() {
			$(this).find('button').prop('disabled', false);
		},
		success: (data) => {
			data = JSON.parse(data);

			if (data.message_type) {
				$.mbNotify(data.message, data.message_type);

				if (data.message_type == 'success') {

					// agenda created
					if ($(e.target).prop('id') == 'create-agenda') {
						$('#agenda-list tbody').append($.templates('#agenda-row').render(data.data));
						$('#agenda-list tbody tr:last-child').effect("highlight", {}, 3000);
					}

					// Homework created
					if ($(e.target).prop('id') == 'create-homework') {
						$('#homework-list tbody').append($.templates('#homework-row').render(data.data));
						$('#homework-list tbody tr:last-child').effect("highlight", {}, 3000);

						if ($('.table-monitor-homework').length > 0) {
							console.log(data.data, $.templates('#monitor-homework-row').render(data.data));
							$('.table-monitor-homework tbody').append($('#monitor-homework-row').render(data.data));
							$('.table-monitor-homework tbody tr:last-child').effect("highlight", {}, 3000);
						}
					}

					// Meeting updated
					if ($(e.target).prop('id') == 'form-update-meeting') {
						// Gather form data
						var meeting_name = $('#form-update-meeting input[name="name"]').val();
						var lang_status = $('#form-update-meeting select[name="status"] option:selected').text();
						var status = $('#form-update-meeting select[name="status"] option:selected').val();
						var goal = $('#form-update-meeting textarea[name="goal"]').val();
						var owner_id = $('#form-update-meeting input[name="owner_id"]').val();
						var team = $('#form-update-meeting input[name="team"]').val().split(',');
						var owner_html = $('.meeting-detail .owner').html();
						var resource_html = '';
						project_members.concat(anonymous_members).forEach((item) => {
							var i = team.indexOf(item.email);
							if (i >= 0) {
								resource_html += '<li>\
													<img class="user-avatar" title="'+ item.name + '" src="'+ item.avatar + '" style="width: 24px; height: 24px">\
													<span class="user-name">'+ (typeof(item.name) != 'undefined' ? item.name : item.email) + '</span>\
													<span class="badge badge-'+ item.cost_of_time + ' badge-bordered pull-right">'+ item.cost_of_time_name +'</span>\
												</li>';
								team.splice(i, 1);
							}
						});

						team.forEach((email) => {
							resource_html += '<li>\
												<img class="user-avatar" title="'+ email + '" src="//www.gravatar.com/avatar/?d=identicon" style="width: 24px; height: 24px">\
												<span class="user-name">'+ email + '</span>\
												<span class="badge badge-'+ default_cost_of_time + ' badge-bordered pull-right">'+ default_cost_of_time_name +'</span>\
											</li>';
						});

						// Update view
						$('#meeting-name').text(meeting_name);
						$('.meeting-detail .status').html('<span class="label label-'+ status +' label-bordered" id="meeting-status" data-status="'+ status +'" data-is-owner="'+ $('#meeting-status').data('is-owner') +'">'+ lang_status +'</span>');
						$('.meeting-detail .owner').html(owner_html);
						$('.detail-goal').html(goal);
						$('.detail-goal').readmore(rm_option);
						$('#meeting-resource').html(resource_html);

						// Hide\Show Button for Owner
						if (owner_id == <?php e($current_user->user_id) ?>) {
							$('.open-meeting-monitor').removeClass('hidden');
						} else {
							$('.open-meeting-monitor').addClass('hidden');
						}
					} 
				}
			}
			
			if (data.close_modal == 0) {
				if ($('.modal.in').length) {
					$('.modal.in .modal-content').html(data.modal_content);
				} else {

					if (data.id) {
						$(data.id + ' .modal-content').html(data.modal_content);
						$(data.id).modal('show');
					} else {
						$('.modal .modal-content').html(data.modal_content);
						$('.modal').modal('show');
					}
				}
			} else {
				$('.modal.in:last .modal-content').html('');
				$('.modal.in:last').modal('hide');
			}
		}
	});
});

// open meeting evaluator
$('#open-meeting-evaluator').click((e) => {
	e.preventDefault();
	var is_owner = $('#open-meeting-evaluator').data('is-owner');
	if (is_owner == 0) {
		swal({
			title: '<?php echo lang('st_waiting') ?>',
			text: '<?php echo lang('st_waiting_evaluator') ?>',
			allowEscapeKey: false,
			imageUrl: '<?php echo Template::theme_url('images/clock.svg') ?>',
			showConfirmButton: false
		});

		var interval = setInterval(function(){
			$.get('<?php echo site_url('meeting/check_state/' . $meeting_key) ?>').done(function(data) {
				if (data == 1) {
					clearInterval(interval);
					swal.close();

					$.get('<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>').done(function(data) {
						data = JSON.parse(data);
						$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
						$('.modal-monitor-evaluator').modal({
							backdrop: 'static'
						});
					});
				}
			});
		}, 3000);
	} else {
		$.get('<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>').done(function(data) {
			data = JSON.parse(data);
			$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
			$('.modal-monitor-evaluator').modal({
				backdrop: 'static'
			});
		});
	}
});

$(document).ready(function() {
	<?php if (! empty($chosen_agenda)) : ?>
	$('.modal').modal('hide');
	$('#agenda-modal').modal({
		backdrop: 'static'
	});
	<?php endif ?>

	$(function() {
		var current_info = $('#current-data').html();
		var check = setInterval(function() {
			console.log('checking...');
			$.get(location.href).done(function(data) {
				if (data != current_info) {
					console.log('need to refresh!');
					// console.log('current info:', current_info);
					// console.log('data:', data);
					$('.refresh-asking').fadeIn();
					clearInterval(check);
				} else {
					console.log('no need to refresh!');
				}
			});
		}, 60000);
	});
});
