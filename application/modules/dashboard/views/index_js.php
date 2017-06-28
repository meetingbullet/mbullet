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