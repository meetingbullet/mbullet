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

	$.get('<?php e(site_url('meeting/edit/' . (empty($is_private) ? $meeting_key : $meeting_id))) ?>', (data) => {
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
	$(e.target).find('button').prop('disabled', true);

	$.ajax({
		type: "POST",
		url: $(e.target).attr('action'),
		data: data,
		complete: function() {
			$(e.target).find('button').prop('disabled', false);
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

						if (team.indexOf('<?php echo $current_user->email ?>') == '-1') {
							team.push('<?php echo $current_user->email ?>');
						}

						project_members.concat(anonymous_members).forEach((item) => {
							var i = team.indexOf(item.email);
							if (i >= 0) {
								if (typeof(item.id) == 'undefined' || item.id != owner_id) {
									resource_html += '<li>\
														<img class="user-avatar" title="'+ item.name + '" src="'+ item.avatar + '" style="width: 24px; height: 24px">\
														<span class="user-name">'+ (typeof(item.name) != 'undefined' ? item.name : item.email) + '</span>\
														<span class="badge badge-'+ item.cost_of_time + ' badge-bordered pull-right">'+ item.cost_of_time_name +'</span>\
													</li>';
								}
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
	// var is_owner = $('#open-meeting-evaluator').data('is-owner');
	// if (is_owner == 0) {
	// 	swal({
	// 		title: '<?php echo lang('st_waiting') ?>',
	// 		text: '<?php echo lang('st_waiting_evaluator') ?>',
	// 		allowEscapeKey: false,
	// 		imageUrl: '<?php echo Template::theme_url('images/clock.svg') ?>',
	// 		showConfirmButton: false
	// 	});

	// 	var interval = setInterval(function(){
	// 		$.get('<?php echo site_url('meeting/check_state/' . $meeting_key) ?>').done(function(data) {
	// 			if (data == 1) {
	// 				clearInterval(interval);
	// 				swal.close();

	// 				$.get('<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>').done(function(data) {
	// 					data = JSON.parse(data);
	// 					$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
	// 					$('.modal-monitor-evaluator').modal({
	// 						backdrop: 'static'
	// 					});
	// 				});
	// 			}
	// 		});
	// 	}, 3000);
	// } else {
	// 	$.get('<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>').done(function(data) {
	// 		data = JSON.parse(data);
	// 		$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
	// 		$('.modal-monitor-evaluator').modal({
	// 			backdrop: 'static'
	// 		});
	// 	});
	// }

	// dont need to wait for owner evaluate any more
	$.get('<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>').done(function(data) {
		data = JSON.parse(data);

		if (data.close_modal === 0) {
			$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
			$('.modal-monitor-evaluator').modal({
				backdrop: 'static'
			});
		}

		if (data.message_type) {
			$.mbNotify(data.message, data.message_type);
		}
	});
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

$(document).on('click', '#agenda-list tbody tr.editable', function() {
	var that = $(this);
	<?php if (empty($is_private)) : ?>
	var url = '<?php echo site_url('agenda/edit/') ?>' + that.find('td:first-child').text().trim();
	<?php else : ?>
	var url = '<?php echo site_url('agenda/edit/') ?>' + that.data('agenda-id');
	<?php endif; ?>
	console.log(url);
	$.mbOpenModalViaUrl('edit-agenda', url);
});

$(document).on('click', '#edit-agenda [type=submit]', function(e) {
	e.preventDefault();

	var that = $(this);
	var form = that.closest('form');
	var data = form.serialize();

	// Since serialize does not include form's action button, 
	// we need to add it on our own.
	data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

	form.find('button').attr('disabled', 'disabled');
	$.ajax({
		type: "POST",
		url: form.attr('action'),
		data: data,
		complete: function() {
			form.find('button').removeAttr('disabled');
		},
		success: function(data) {
			data = JSON.parse(data);

			if (data.close_modal === 0) {
				$('.modal .modal-content').html(data.modal_content);
			} else {
				$('.modal').modal('hide');
			}

			if (data.message_type) {
				$.mbNotify(data.message, data.message_type);

				if (data.message_type == 'success') {
					console.log(data);
					$(`#agenda-list tbody tr[data-agenda-id=${data.data.agenda_id}]`)
					.find('td')
					.wrapInner('<div style="display: block;" />')
					.parent()
					.find('td > div')
					.slideUp('fast', function(){
						$(`#agenda-list tbody tr[data-agenda-id=${data.data.agenda_id}]`).remove();
						$('#agenda-list tbody').append($.templates('#agenda-row').render(data.data));
						$('#agenda-list tbody tr:last-child').effect("highlight", {}, 3000);
					});
				}
			}
		}
	});
});

$(document).on('click', '#agenda-list .close-btn', function(e) {
	e.stopPropagation();
	var that = $(this);
	swal({
		title: "Are you sure?",
		text: "You will not be able to recover this!",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Yes, delete it!",
		closeOnConfirm: false
	},
	function(){
		<?php if (empty($is_private)) : ?>
		var agenda_key = that.closest('tr').find('td:first-child').text();
		var url = '<?php echo site_url('agenda/delete/') ?>' + agenda_key;
		<?php else : ?>
		var agenda_id = that.closest('tr').data('agenda-id');
		var url = '<?php echo site_url('agenda/delete/') ?>' + agenda_id;
		<?php endif; ?>
		$.get({url}).done(function(data) {
			swal.close();
			data = JSON.parse(data);
			if (data.status == 1) {
				that.closest('tr')
				.find('td')
				.wrapInner('<div style="display: block;" />')
				.parent()
				.find('td > div')
				.slideUp('fast', function(){
					$(`#agenda-list tbody tr[data-agenda-id=${data.data.agenda_id}]`).remove();
				});
			}

			$.mbNotify(data.message, data.message_type);
		});
	});
});

$(document).on('click', '#homework-list tbody tr.editable', function() {
	var that = $(this);
	var url = '<?php echo site_url('homework/edit/') ?>' + that.data('homework-id');
	console.log(url);
	$.mbOpenModalViaUrl('edit-homework', url);
});

$(document).on('click', '#edit-homework [type=submit]', function(e) {
	e.preventDefault();

	var that = $(this);
	var form = that.closest('form');
	var data = form.serialize();

	// Since serialize does not include form's action button, 
	// we need to add it on our own.
	data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

	form.find('button').attr('disabled', 'disabled');
	$.ajax({
		type: "POST",
		url: form.attr('action'),
		data: data,
		complete: function() {
			form.find('button').removeAttr('disabled');
		},
		success: function(data) {
			data = JSON.parse(data);

			if (data.close_modal === 0) {
				$('.modal .modal-content').html(data.modal_content);
			} else {
				$('.modal').modal('hide');
			}

			if (data.message_type) {
				$.mbNotify(data.message, data.message_type);

				if (data.message_type == 'success') {
					console.log(data);
					$(`#homework-list tbody tr[data-homework-id=${data.data.homework_id}]`)
					.find('td')
					.wrapInner('<div style="display: block;" />')
					.parent()
					.find('td > div')
					.slideUp('fast', function(){
						$(`#homework-list tbody tr[data-homework-id=${data.data.homework_id}]`).remove();
						$('#homework-list tbody').append($.templates('#homework-row').render(data.data));
						$('#homework-list tbody tr:last-child').effect("highlight", {}, 3000);
					});
				}
			}
		}
	});
});

$(document).on('click', '#homework-list .close-btn', function(e) {
	e.stopPropagation();
	var that = $(this);

	swal({
		title: "Are you sure?",
		text: "You will not be able to recover this!",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Yes, delete it!",
		closeOnConfirm: false
	},
	function(){
		var homework_id = that.closest('tr').data('homework-id');
		var url = '<?php echo site_url('homework/delete/') ?>' + homework_id;
		$.get({url}).done(function(data) {
			swal.close();
			data = JSON.parse(data);
			if (data.status == 1) {
				that.closest('tr')
				.find('td')
				.wrapInner('<div style="display: block;" />')
				.parent()
				.find('td > div')
				.slideUp('fast', function(){
					$(`#homework-list tbody tr[data-homework-id=${homework_id}]`).remove();
				});
			}

			$.mbNotify(data.message, data.message_type);
		});
	});
});

<?php if (empty($is_private)) : ?>
// add date time picker in normal modal
$(document).on('click', '#update-meeting', function(e) {
	e.preventDefault();
	console.log('clicked');
	var url = '<?php echo site_url('meeting/date_picker') ?>';
	if ($('#update-meeting').closest('.form-ajax').find('input#meeting-scheduled-start-time').length) {
		url += '?selected_date=' + encodeURIComponent($('#update-meeting').closest('.form-ajax').find('input#meeting-scheduled-start-time').val());
	}
	$.mbOpenModalViaUrl('meeting-date-time-picker', url, 'modal-sm');
});

// update scheduled start time from date picker
$(document).on('dp.change', '#dt-picker', function() {
	var scheduled_start_time = $(this).data("DateTimePicker").date().format('YYYY-MM-DD HH:mm:ss');
	$('input#meeting-scheduled-start-time').val(scheduled_start_time);
});

$(document).on('click', '#meeting-date-time-picker .modal-footer button', function() {
	if ($(this).attr('id') == 'dt-later') {
		$('input#meeting-scheduled-start-time').val('');
	}

	$('#meeting-date-time-picker').modal('hide');
	$('#update-meeting').closest('.form-ajax').submit();
});
<?php endif ?>

// show message if evaluated
<?php if (! empty($evaluated)) : ?>
$.mbNotify('<?php echo lang('st_meeting_already_evaluated') ?>', 'info');
<?php endif ?>

// get anchor value
function getAnchor(url) {
	if (!url) url = window.location.href;
	var index = url.indexOf("#");

	if (index == -1) {
		return null;
	}

	var anchor = url.substring(url.indexOf("#") + 1);
	return anchor;
}

var anchor = getAnchor();
if (anchor == 'join_meeting') {
	$('a.mb-open-modal.open-meeting-monitor').click();
}

$(document).on('click', '#homework-list tr .homework-status', function(e) {
	e.stopPropagation();
})

$(document).on('click', '#homework-list tr .homework-status .btn-update-homework-status', (e) => {
	$.post("<?php echo site_url('homework/ajax_edit') ?>", {
		pk: $(e.target).data('pk'),
		name: 'status',
		value: $(e.target).data('value'),
	}, (data) => {
		data = JSON.parse(data);

		$.mbNotify(data.message, data.message_type);

		if (data.message_type == 'success') {
			var btn_status = $(e.target).parents('.btn-group').children('.btn-status');
			var btn_status_caret = $(e.target).parents('.btn-group').children('.btn.dropdown-toggle');

			$(btn_status).text( $(e.target).text() );
			$(btn_status).prop('class', 'btn btn-status label-' + $(e.target).data('value'));
			$(btn_status).data('status', $(e.target).data('value'));
			$(btn_status).attr('data-status', $(e.target).data('value'));
			$(btn_status_caret).prop('class', 'btn dropdown-toggle label-' + $(e.target).data('value'));

			$(e.target).parents('ul').find('.btn-update-homework-status').removeClass('hidden');
			$(e.target).addClass('hidden');
		}
	}).fail((data) => {
		data = JSON.parse(data.responseText);

		$.mbNotify(data.message, data.message_type);
		console.log(data);
	});
});

$(document).on('click', '.btn-block .an-btn', function(e) {
	$(this).attr('disabled', 'disabled');
});

$(document).on('click', '.modal-monitor-evaluator button.close, #meeting-monitor-modal button.close, #meeting-decider-modal button.close', function(e) {
	location.reload();
});