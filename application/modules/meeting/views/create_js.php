var default_cost_of_time = '<?php echo $default_cost_of_time ?>';
var default_cost_of_time_name = '<?php echo $default_cost_of_time_name ?>';

var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
				'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

var project_members = [
	<?php foreach($project_members as $user): 
		if (strstr($user->avatar, 'http') === false) {
			$user->avatar = avatar_url($user->avatar, $user->email);
		}

		if (! empty($meeting_members)) {
			$index = array_search($user->email, $meeting_members);
			if ($index !== false) {
				unset($meeting_members[$index]);
			}
		}
	?>
	{
		id: '<?php e($user->user_id)?>', 
		name: '<?php e($user->first_name . ' ' . $user->last_name)?>', 
		avatar: '<?php echo $user->avatar?>', 
		cost_of_time: <?php e($user->cost_of_time)?>,
		cost_of_time_name: '<?php e(empty($user->cost_of_time_name) ? 'N/A' : $user->cost_of_time_name)?>',
		email: '<?php e($user->email)?>', 
	},
	<?php endforeach; ?>
];

var anonymous_members = [
	<?php if (! empty($meeting_members)) :foreach ($meeting_members as $anonymous_email) : ?>
	{
		email: '<?php echo $anonymous_email ?>',
		cost_of_time: default_cost_of_time,
		cost_of_time_name: default_cost_of_time_name,
		avatar: '<?php echo avatar_url(null, $anonymous_email) ?>'
	},
	<?php endforeach; endif; ?>
];

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
	options: project_members,
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
	valueField: 'email',
	labelField: 'name',
	searchField: ['name'],
	options: project_members.concat(anonymous_members),
	render: {
		item: function(item, escape) {
			return '<div>' +
				(item.avatar ? '<img src="' + item.avatar + '" class="avatar" />' : '') +
				(item.name ? '<span class="name">' + escape(item.name) + '</span>' : '') +
				(item.email ? '<span class="email">(' + escape(item.email) + ')</span>' : '') +
			'</div>';
		},
		option: function(item, escape) {
			var label = item.name || item.email;
			var caption = item.name ? item.email : null;
			return '<div>' +
				(item.avatar ? '<img src="' + item.avatar + '" class="avatar" />' : '') +
				'<span class="name">' + escape(label) + '</span>' +
				(caption ? '<span class="caption">(' + escape(caption) + ')</span>' : '') +
			'</div>';
		}
	},
	createFilter: function(input) {
		var match, regex;

		// email@address.com
		regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
		match = input.match(regex);
		if (match) return !this.options.hasOwnProperty(match[0]);

		// name <email@address.com>
		regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
		match = input.match(regex);
		if (match) return !this.options.hasOwnProperty(match[2]);

		return false;
	},
	create: function(input) {
		if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
			return {email: input};
		}
		var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
		console.log(match);
		if (match) {
			return {
				email : match[2],
				name  : match[2]
			};
		}
		alert('Invalid email address.');
		return false;
	}
});

/*$('#meeting-in').change(function() {
	var val = $('#meeting-in option:selected').val();
	if (val != 'other') {
		if ($('input#in').css('display') != 'none') {
			$('input#in, div#in-unit').fadeOut({
				done: function() {
					$('input#in').val(val);
				}
			});
		} else {
			$('input#in').val(val);
		}
	} else {
		$('input#in, div#in-unit').fadeIn();
	}
});

$('#meeting-in').change();*/

// Input manually handler
$(document).on('change.meeting.create', '#meeting-in', function() {
	var in_val = $(this).val();
	if ($(this).val() == 'other') {
		$('input#in, div#in-unit').fadeIn('fast');
	} else {
		if ($('input#in').css('display') != 'none') {
			$('input#in, div#in-unit').fadeOut({
				done: function() {
					$('input#in').attr('value', in_val);
				}
			});
		} else {
			$('input#in').attr('value', in_val);
		}
	}
});

$('#meeting-in').change();

if ($('#meeting-in option:selected').val() == 'other') {
	$('input#in, div#in-unit').show();
}

<?php if (! empty($this->input->get('in'))) : ?>
$('#meeting-in option[value=other]').attr('selected', 'selected').change();
$('#in').val('<?php echo $this->input->get('in') ?>');
<?php endif ?>