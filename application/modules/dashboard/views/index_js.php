$('.mb-popover-project').on('shown.bs.popover', function() {
	$('.mb-editable').editable();
	console.log('ok');
})

$('#homework').click(function(e) {
	e.preventDefault();
	$(this).popover({
		html: true, 
		content: function() {
			return $('#homework-popover').html();
		}
	});

	$('[data-toggle="popover"]').not(this).popover('hide');
})

$('.mb-popover-project').click(function(e) {
	e.preventDefault();
	$(this).popover({
		html: true, 
		content: function() {
			return $('#popover-project-' + $(this).data('project-id')).html();
		}
	});

	$('[data-toggle="popover"]').not(this).popover('hide');
})

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

$(document).on('click', '.btn-time + ul > li > a', function(e) {
	e.preventDefault();
	var time = parseFloat( $(this).parent().parent().data('minute') );
	var parent = $(this).parents('.time-wrapper');

	switch ($(this).data('option')) {
		case 'minute':
			$(parent).find('.btn-time > .number').text(Math.round(time * 100) / 100);
			break;
		case 'hour':
			$(parent).find('.btn-time > .number').text(Math.round(time / 60 * 100) / 100);
			break;
		case 'day':
			$(parent).find('.btn-time > .number').text(Math.round(time / 60 / 24 * 10) / 10);
	}

	console.log($(parent).find('.btn-time > .number'));

	$(parent).find('.btn-time > .text').text($(this).text());
})