$(document).ready(function() {
	$(document).on('focus', 'input#owner-name, input#action-member-name', function() {
		if ($(this).attr('id') == 'owner-name') {
			// auto complete for owner
			$(this).autocomplete({
				source: function(request, response) {
					var url = $('form #owner-name').data('get-owner-url');
					$.ajax({
						method: 'get',
						url: url,
						dataType: "json",
						data: {
							member_name: request.term
						},
						success: function(data) {
							members = [];
							data.forEach(function(item, index) {
								members[index] = {
									label: item.full_name,
									value: item.user_id
								};
							});
							response(members);
						}
					});
				},
				delay: 500,
				minLength: 2,
				appendTo: '#owner-warpper',
				select: function(event, ui) {
					event.preventDefault();
					$('#owner-id').val(ui.item.value);
					$('#owner-name').val(ui.item.label);
				},
				focus: function(event, ui) {
					event.preventDefault();
					$('#owner-name').val(ui.item.label);
				}
			});
		} else {
			// auto complete for resource
			$(this).autocomplete({
				source: function(request, response) {
					var url = $('form #action-member-name').data('get-resource-url');
					$.ajax({
						method: 'get',
						url: url,
						dataType: "json",
						data: {
							member_name: request.term
						},
						success: function(data) {
							members = [];
							data.forEach(function(item, index) {
								members[index] = {
									label: item.full_name,
									value: item.user_id
								};
							});
							response(members);
						}
					});
				},
				delay: 500,
				minLength: 2,
				appendTo: '#action-members-warpper',
				select: function(event, ui) {
					event.preventDefault();
					$('#action-member-name').val(ui.item.label);
					var member_ids = $('#action-members').val();
					member_ids = JSON.parse(member_ids);

					var in_array = false;
					for (i = 0; i < member_ids.length; i++) {
						if (member_ids[i].value == ui.item.value) {
							in_array = true;
							break;
						}
					}

					if (in_array == false) {
						member_ids.push(ui.item);
						$('#action-members-warpper .members').append(
							`<span data-value="${ui.item.value}" data-label="${ui.item.label}" class="label label-success label-bordered">
								${ui.item.label}&nbsp;
								<i class="ion-ios-close-outline"></i>
							</span>`
						);
					}

					member_ids = JSON.stringify(member_ids);
					$('#action-members').val(member_ids);
				},
				focus: function(event, ui){
					event.preventDefault();
					$('#action-member-name').val(ui.item.label);
				}
			});
		}
	});

	$(function() {
		var member_ids = $('#action-members').val();
		member_ids = JSON.parse(member_ids);

		for (i = 0; i < member_ids.length; i++) {
			$('#action-members-warpper .members').append(
				`<span data-value="${member_ids[i].value}" data-label="${member_ids[i].label}" class="label label-success label-bordered">
					${member_ids[i].label}&nbsp;
					<i class="ion-ios-close-outline"></i>
				</span>`
			);
		}
	});

	$(document).on('click', '#action-members-warpper .members span.label.label-success.label-bordered i', function() {
		var removed_span = $(this).parent();
		var member_ids = $('#action-members').val();
		member_ids = JSON.parse(member_ids);

		for (i = 0; i < member_ids.length; i++) {
			if (member_ids[i].value == removed_span.data('value')) {
				member_ids.splice(i, 1);
				member_ids = JSON.stringify(member_ids);
				$('#action-members').val(member_ids);
				break;
			}
		}

		removed_span.remove();
	});
});