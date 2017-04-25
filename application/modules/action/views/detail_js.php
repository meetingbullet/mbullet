Selectize.define('select-member', function(options) {
	var self = this;

	// Override updatePlaceholder method to keep the placeholder
	this.updatePlaceholder = (function() {
		var original = self.updatePlaceholder;
		return function() {
			// do your logic
			return false;
			// return original.apply(this, arguments);
		};
	})();
});


// Ajax add action team member
var select_member = $('#team-member').selectize({
	onItemAdd: function (item) {
		$.post('<?php echo site_url('action/add_team_member') ?>', {
			'action_id': <?php echo $action->action_id ?>,
			'user_id': item
		}, function (res) {
			if (res == 0) {
				select_member[0].selectize.removeItem(item);
			}
		});
	},
	onItemRemove: function (item) {
		$.post('<?php echo site_url('action/remove_team_member') ?>', {
			'action_id': <?php echo $action->action_id ?>,
			'user_id': item
		}, function (res) {
			if (res == 0) {
				select_member[0].selectize.addItem(item);
			}
		});
	},
	plugins: ['remove_button', 'select-member'],
	persist: false,
	maxItems: null,
	valueField: 'id',
	labelField: 'name',
	searchField: ['name'],
	options: [
		<?php foreach($oragnization_members as $user): 
			if (strstr($user->avatar, 'http') === false) {
				$user->avatar = avatar_url($user->avatar, $user->email);
			}
		?>
		{id: '<?php e($user->user_id)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php e($user->avatar)?>'},
		<?php endforeach; ?>
	],
	render: {
		item: function(item, escape) {
			return '<div>' +
				'<img' + (item.avatar ? ' src="' + item.avatar + '"' : '')  + ' class="avatar" />' +
				(item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
			'</div>';
		},
		option: function(item, escape) {
			return '<div>' +
				'<img' + (item.avatar ? ' src="' + item.avatar + '"' : '')  + ' class="avatar" />' +
				(item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
			'</div>';
		}
	},
	create: false
});

// Add step
$('#add-step').click((e) => {
	e.preventDefault();

	$.get('<?php e(site_url('step/create/' . $action_key)) ?>', (data) => {
		data = JSON.parse(data);
		console.log(data.modal_content);
		$('.modal .modal-content').html(data.modal_content);
		$('.modal').modal();
	});

});

// Set form-ajax to work inside a modal
$(document).on("submit", '.form-ajax', (e) => {
	e.preventDefault();

	var method = $(e.target).attr('method') ? $(e.target).attr('method') : 'post';
	var data = $(e.target).serialize();

	// Since serialize does not include form's action button, 
	// we need to add it on our own.
	data += '&' + $(e.target).find('[type="submit"]').attr('name') + '=';

	$.ajax({
		type: "POST",
		url: $(e.target).attr('action'),
		data: data,
		success: (data) => {
			data = JSON.parse(data);

			if (data.close_modal === 0) {
				$('.modal .modal-content').html(data.modal_content);
				$('.modal').modal('show');
			} else {
				$('.modal').modal('hide');
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
				}
			}
		}
	});
});