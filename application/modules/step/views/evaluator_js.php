$(document).ready(function() {
	$('#submit_evaluator').click(function(e) {
		e.preventDefault();
		$.post({
			url: $('#step-name').data('evaluator-url'),
			data: $('.form-ajax').serialize()
		}).done(function(data) {console.log(data);
			data = JSON.parse(data);
			if (data.close_modal === 0) {
				$('.modal-monitor-evaluator .modal-content').html(data.modal_content);
			} else {
				$('.modal-monitor-evaluator').modal('hide');
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
					location.reload();
				}
			}
		});
	});

	$(".rating label").click(function(){
		$(this).parent().find("label").css({"color": "#D8D8D8"});
		$(this).css({"color": "#FFED85"});
		$(this).nextAll().css({"color": "#FFED85"});
		var input_id = $(this).attr('for');
		$(this).parent().find('#' + input_id).click();
	});
})