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

$('.owner-id').selectize({
	plugins: ['select-member'],
	persist: false,
	maxItems: 1,
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

$('.team').selectize({
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