<div>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('pj_add_new_member'))?></h4>
	</div> <!-- end MODAL-HEADER -->
	<?php echo form_open($this->uri->uri_string(), ['class' => 'form-ajax']) ?>
	<div class='container-fluid modal-body'>
	
	<?php echo mb_form_input('text', 'email', lang('pj_invite_team'), false, '', 'an-tags-input js-input-tags', '', lang('pj_member_email')) ?>
	</div>

	<div class="modal-footer">
		<button type="submit" name="save" id="save" class="an-btn an-btn-primary"><?php e(lang('pj_add'))?></button>
		<a href="#" class="an-btn an-btn-primary-transparent" data-dismiss="modal"><?php e(lang('pj_cancel'))?></a>
	</div>
	<?php echo form_close() ?>
</div>
<script>
	var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
					'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

	$('.js-input-tags').selectize({
		persist: false,
		maxItems: 1,
		valueField: 'email',
		labelField: 'name',
		searchField: ['name', 'email'],
		options: [
			<?php foreach($invite_emails as $user): 
				if (strstr($user->avatar, 'http') === false) {
					$user->avatar = avatar_url($user->avatar, $user->email);
				}
			?>
			{email: '<?php e($user->email)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php echo $user->avatar?>'},
			<?php endforeach; ?>
		],
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
					name  : $.trim(match[1])
				};
			}
			alert('Invalid email address.');
			return false;
		}
	});
</script>