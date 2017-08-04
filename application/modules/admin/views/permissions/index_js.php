$(document).ready(function(){
	$("#save-permissions").click(function(){
		var allChecked = [];
		$("input:checked").each(function(){
			allChecked.push($(this).val());
		});
		$.post("<?php echo site_url('admin/permissions/save_changes/') ?>", { data: allChecked }, function(data, status){
			data = JSON.parse(data);
			$.mbNotify(data.message, data.message_type);
		})
	});

	$(".list-name").click(function(){
			var check = false;
			$(this).parent().children().children("input").each(function(index, element){
				if (!$(element).is(':disabled') && !$(element).is(':checked')) {
					check = true;
				}
			});
			if (check) {
			$(this).parent().children().children("input").each(function(index, element){
				if (!$(element).is(':disabled')) {
					$(element).prop('checked', 'checked');
				}
			});
			} else {
			$(this).parent().children().children("input").each(function(index, element){ 
					if (!$(element).is(':disabled')) {
						$(element).prop('checked', '');
					}
				});
			}

		});
});