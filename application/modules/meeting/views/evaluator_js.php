$(document).ready(function() {
	$('#submit_evaluator').click(function(e) {
		e.preventDefault();
		$.post({
			url: '<?php echo site_url('meeting/evaluator/' . $meeting_key) ?>',
			data: $('.form-ajax').serialize()
		}).done(function(data) {
			data = JSON.parse(data);
			if (data.close_modal === 0) {
				$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
			} else {
				$('.modal-monitor-evaluator').modal('hide');
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

	$(".rating label").click(function(){
		$(this).parent().find("label").css({"color": "#D8D8D8"});
		$(this).css({"color": "#FFED85"});
		$(this).nextAll().css({"color": "#FFED85"});
		var input_id = $(this).attr('for');
		$(this).parent().find('#' + input_id).click();
	});

	$(".meeting-rating label").click(function(){
		$(this).parent().find("label").css({"color": "#D8D8D8"});
		$(this).css({"color": "#FFED85"});
		$(this).nextAll().css({"color": "#FFED85"});
		var input_id = $(this).attr('for');
		$(this).parent().find('input[type=radio]').removeAttr('checked');
		$(this).parent().find('input[type=radio]#' + input_id).attr('checked', '');
	});

	// Enable jQuery tooltip
	$('[data-toggle="tooltip"]').tooltip(); 
})