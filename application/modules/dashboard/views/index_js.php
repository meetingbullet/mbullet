// Calendar
$('#meeting-calendar').fullCalendar({
	header: {
		center: 'prev, today, next ',
		left: 'title',
		right: 'month,agendaWeek,agendaDay,listWeek'
	},
	
	navLinks: true,
	firstDay: 1, // Monday
	aspectRatio: 1, // content Width-to-Height
	editable: false,
	eventLimit: true, // allow "more" link when too many events
	eventRender: function(event, element) { 
		if (event.end) {
			if (element.attr('href')) {
				element.attr("data-modal-id", "meeting-preview-modal");
				element.addClass("mb-open-modal");
			} else {
				element.find('a').attr("data-modal-id", "meeting-preview-modal");
				element.find('a').addClass("mb-open-modal");

				element.click(function(e) {
					e.preventDefault();
					$.mbOpenModalViaUrl('meeting-preview-modal', event.url);
					return false;
				})
			}
		}
	},
	events: <?php echo json_encode($meeting_calendar) ?>,
});

// rating
$(".todo-rating label").click(function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('input[type=radio]').removeAttr('checked');
	$(this).parent().find('input[type=radio]#' + input_id).attr('checked', '');
});


$(document).ready(function() {
	
	// Open homework modal
	$('.homework-info').click(function(e){
		e.preventDefault();

		var modal_content = `
		<div class="row">
			<div class="col-xs-12">
				<div class="row" style="padding-bottom: 10px;">
					<div class="col-xs-4"><label><?php echo lang("st_description") ?>:</label></div>
					<div class="col-xs-8">`+ $(this).data('description') +`</div>
				</div>
			</div>
			<div class="col-xs-12">
				<div class="row" style="padding-bottom: 10px;">\
					<div class="col-xs-4"><label><?php echo lang("st_assignee") ?>:</label></div>\
					<div class="col-xs-8">`+ $(this).data('members') +`</div>\
				</div>
			</div>
			<div class="col-xs-12">
				<div class="row">\
					<div class="col-xs-4"><label><?php echo lang("st_status") ?>:</label></div>\
					<div class="col-xs-8"><span class="label label-bordered label-` + $(this).data('status') + `">` + $(this).data('lang-status') + `</span></div>\
				</div>
			</div>
		</div>`;

		$.mbOpenModal('homework-info-modal', $(this).data('title'), modal_content);
	});


	$(".my-todo").on("click", ".submit", function(e) {
		e.preventDefault();
		var submit_btn = $(this);

		if (submit_btn.hasClass('homework')) {
			var todo_type = 'homework';
		}

		if (submit_btn.hasClass('evaluate')) {
			var todo_type = 'evaluate';
		}

		if (todo_type == 'homework') {
			var url = submit_btn.parent().data('url');
			var data = {};
			data.pk = submit_btn.parent().data('homework-id');
			data.value = submit_btn.data('status');
			data.name = 'status'
			console.log(url, data);
		}

		if (todo_type == 'evaluate') {
			var url = submit_btn.parent().parent().parent().data('url');
			var data = {};
			data.rate = submit_btn.parent().parent().find('input[type=radio]:checked').val();

			if (typeof(data.rate) != 'undefined') {
				data.meeting_id = submit_btn.parent().parent().parent().data('meeting-id');

				if (submit_btn.parent().parent().parent().hasClass('user')) {
					data.user_id = submit_btn.parent().parent().parent().data('user-id');
				}

				if (submit_btn.parent().parent().parent().hasClass('agenda')) {
					data.agenda_id = submit_btn.parent().parent().parent().data('agenda-id');
				}

				if (submit_btn.parent().parent().parent().hasClass('homework')) {
					data.homework_id = submit_btn.parent().parent().parent().data('homework-id');
				}
				console.log(url, data);
			} else {
				var error = '<?php echo lang("db_rate_needed") ?>';
			}

			if (typeof(error) == 'undefined') {
				$.post({
					url: url,
					data: data,
				}).done(function(data) {console.log(data);
					data = JSON.parse(data);
					if (data.message_type == 'success') {
						submit_btn.closest('.item').slideUp();
					}

					$.mbNotify(data.message, data.message_type);
				}).fail(function(xhr, statusText) {
					console.log(xhr.status);
					$.mbNotify(data.message, data.message_type);
				});
			} else {
				$.mbNotify(error, 'danger');
			}
		}
	});

	$(function() {
		var current_info = $('#current-data').html();
		var check = setInterval(function() {
			console.log('checking...');
			$.get("<?php echo site_url('dashboard') ?>").done(function(data) {
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
})

// Decide
$('.submit-confirm-status').click(function(e) {
	e.preventDefault();

	var confirm_status_selector = $(this).parent().find('select[name="confirm-status"]');
	var value = $(this).parent().find('select[name="confirm-status"] option:selected').val();
	var pk = $(confirm_status_selector).data('pk');

	if (value != '') {
		$(confirm_status_selector).removeClass('danger');

		$.post('<?php echo site_url("agenda/ajax_edit") ?>', {
			pk,
			name: "confirm_status",
			value
		}, (data) => {
			data = JSON.parse(data);

			$.mbNotify(data.message, data.message_type);

			if (data.message_type == 'success') {
				console.log('Decided, closing');
				$(this).parents('.item').slideUp();
			}
		});
	} else {
		$(confirm_status_selector).addClass('danger');
	}
});

$('select[name="confirm-status"]').change(function() {
	if ( $(this).children('option:selected').val() != '') {
		$(this).removeClass('danger');
	}
});
