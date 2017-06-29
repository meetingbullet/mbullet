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