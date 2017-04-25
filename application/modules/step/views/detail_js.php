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

var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
					'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

var select_member = $('#team-member').selectize({
	onItemAdd: function (item) {
		$.post('<?php echo site_url('action/add_team_member') ?>', {
			'action_id': <?php echo $step->step_id ?>,
			'user_id': item
		}, function (res) {
			if (res == 0) {
				select_member[0].selectize.removeItem(item);
			}
		});
	},
	onItemRemove: function (item) {
		$.post('<?php echo site_url('action/remove_team_member') ?>', {
			'action_id': <?php echo $step->step_id ?>,
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
		<?php foreach($oragnization_members as $user) :
			if (strstr($user->avatar, 'http') === false) {
				$user->avatar = avatar_url($user->avatar, $user->email);
			}
		?>
		{id: '<?php e($user->user_id)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php e($user->avatar)?>'},
		<?php endforeach ?>
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