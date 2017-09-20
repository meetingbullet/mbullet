$(document).off('.evaluator');

$(document).on('click.evaluator', '#submit_evaluator', function(e) {
	e.preventDefault();
	$.post({
		url: '<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>',
		data: $('.form-ajax').serialize()
	}).done(function(data) {
		data = JSON.parse(data);
		if (data.close_modal === 0) {
			$('#meeting-evaluator-modal .modal-content').html(data.modal_content);
		} else {
			$('#meeting-evaluator-modal').modal('hide');
		}

		if (data.message_type) {
			$.mbNotify(data.message, data.message_type);

			if (data.message_type == 'success') {
				// @TODO Refresh Meeting list
				location.reload();
			}
		}
	}).fail(function() {console.log('evaluated failed')});
});

$(document).on("click.evaluator", ".rating label", function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('#' + input_id).click();
});

$(document).on("click.evaluator", ".meeting-rating label", function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('input[type=radio]').removeAttr('checked');
	$(this).parent().find('input[type=radio]#' + input_id).attr('checked', '');
});

// Enable jQuery tooltip
$('[data-toggle="tooltip"]').tooltip();