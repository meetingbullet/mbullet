<?php if (has_permission('Project.Edit.All')): ?>
$('.mb-popover-project').on('shown.bs.popover', function() {
	$('.mb-editable').editable({
		success: function(data) {
			data = JSON.parse(data);

			$.mbNotify(data.message, data.message_type);
			
			if (data.message_type == 'danger') {
				return;
			}

			$(this).data('value', data.value);
			$(this).html(data.value);
		}
	});
})
<?php endif; ?>

$('.mb-popover-project').click(function(e){
	e.preventDefault();
})


$('.mb-popover-project.new').click(function(e){
	var that = this;

	$.get('<?php echo site_url('dashboard/mark_as_read/project/') ?>' + $(this).data('project-id'), (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			$(that).removeClass('new');

			// Remove remaining My Project [new]
			if ($('.badge-new').length == 2) {
				$('.badge-new').fadeOut('fast', function(){
					$(this).remove();
				});
			} else {
				$(that).find('.badge-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '#homework-content .child.new', function(e){
	var that = this;

	$.get('<?php echo site_url('dashboard/mark_as_read/homework/') ?>' + $(this).data('homework-id'), (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			var tr = 'tr[data-homework-id="'+ $(that).data('homework-id') +'"].child';
			$(tr).removeClass('new');

			// Remove remaining menu Homework [new] & My Todo new if all Rates has been read
			if ($('.badge-homework-new').length == 3) {
				$('.badge-homework-new').fadeOut('fast', function(){
					$(this).remove();
				});

				if ($('.badge-rate-new').length == 0) {
					$('.badge-todo-new').remove(); 
				}
			} else {
				$(tr + ' .badge-homework-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '#rate-content .child.new', function(e){
	var that = this;
	var type = $(this).data('mode');
	var object_id = $(this).data('id');
	var user_id = "";

	if (type == 'user') {
		object_id = $(this).data('meeting-id');
		user_id += "/" + $(this).data('id');
	}

	$.get("<?php echo site_url('dashboard/mark_as_read/') ?>"+ type + "/" + object_id + user_id, (data) => {
		data = JSON.parse(data);

		if (data.message_type == 'success') {
			var tr = 'tr[data-id="'+ $(that).data('id') +'"].child.' + type;
			$(tr).removeClass('new');

			// Remove remaining menu Homework [new] & My Todo new if all Rates has been read
			if ($('.badge-rate-new').length == 3) {
				$('.badge-rate-new').fadeOut('fast', function(){
					$(this).remove();
				});

				if ($('.badge-homework-new').length == 0) {
					$('.badge-todo-new').remove(); 
				}
			} else {
				$(tr + ' .badge-rate-new').fadeOut('fast', function(){
					$(this).remove();
				});
			}
		}
	})
});

$(document).on('click', '.btn-confirm-homework', function() {
	var hw_id = $(this).data('homework-id');

	$.post("<?php echo site_url('homework/ajax_edit') ?>", {
		pk: hw_id,
		name: 'status',
		value: 'done'
	}, (data) => {
		data = JSON.parse(data);
		$.mbNotify(data.message, data.message_type);

		$(this)
		.parents('.child')
		.find('td')
		.wrapInner('<div style="display: block;" />')
		.parent()
		.find('td > div')
		.slideUp('fast', function(){
			$(this).parent().parent().remove();
		});

		$('#homework-popover tr.child[data-homework-id="'+ hw_id +'"]').remove();
		$('.homework-counter').text($('.homework-counter').text() - 1);
	})
})

// rating
$(document).on('click', '.todo-rating label', function(){
	$(this).parent().find("label").css({"color": "#D8D8D8"});
	$(this).css({"color": "#FFED85"});
	$(this).nextAll().css({"color": "#FFED85"});
	var input_id = $(this).attr('for');
	$(this).parent().find('input[type=radio]').removeAttr('checked');
	$(this).parent().find('input[type=radio]#' + input_id).attr('checked', '');
});

// evaluate
$(document).on("click", "#rate-content .submit", function(e) {
	e.preventDefault();
	var submit_btn = $(this);

	var todo_type = 'evaluate';

	var url = submit_btn.closest('.child').find('.data').data('url');

	var data = {};
	data.rate = submit_btn.closest('.child').find('.data').find('input[type=radio]:checked').val();

	if (typeof(data.rate) != 'undefined') {
		data.meeting_id = submit_btn.closest('.child').find('.data').data('id');

		if (submit_btn.closest('.child').hasClass('user')) {
			data.user_id = submit_btn.closest('.child').find('.data').data('id');
		}

		if (submit_btn.closest('.child').hasClass('agenda')) {
			data.agenda_id = submit_btn.closest('.child').find('.data').data('id');
		}

		if (submit_btn.closest('.child').hasClass('homework')) {
			data.homework_id = submit_btn.closest('.child').find('.data').data('id');
		}
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
				$(submit_btn)
				.parents('.child')
				.find('td')
				.wrapInner('<div style="display: block;" />')
				.parent()
				.find('td > div')
				.slideUp('fast', function(){
					$(this).parent().parent().remove();
				});
			}

			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});
		}).fail(function(xhr, statusText) {
			console.log(xhr.status);
			$.notify({
				message: data.message
			}, {
				type: data.message_type,
				z_index: 1051
			});
		});
	} else {
		$.notify({
			message: error
		}, {
			type: 'danger',
			z_index: 1051
		});
	}

});