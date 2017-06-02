<?php
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title" id="myModaloneLabel"><?php e(lang('ag_create_agenda')) ?></h4>
</div> <!-- end MODAL-HEADER -->

<?php echo form_open($this->uri->uri_string(), ['class' => 'form-ajax', 'id' => 'create-agenda']) ?>

<div class="container-fluid modal-body">
	<?php echo mb_form_input('text', 'name', lang('ag_name'), true, set_value('name')) ?>
	<?php echo mb_form_input('text', 'description', lang('ag_description'), false, set_value('description')) ?>
	<?php echo mb_form_input('text', 'assignee', lang('ag_assignee'), true, set_value('assignee'), 'team select-member an-tags-input', '', lang('ag_add_team_member')) ?>
</div>

<div class="modal-footer">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('ag_create'))?></button>
	<a href="#" class="an-btn an-btn-primary-transparent" data-dismiss="modal"><?php e(lang('ag_cancel'))?></a>
</div>

<?php echo form_close(); ?>

<script>
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

	$('.team').selectize({
		plugins: ['remove_button', 'select-member'],
		persist: false,
		maxItems: null,
		valueField: 'id',
		labelField: 'name',
		searchField: ['name'],
		options: [
			<?php foreach($organization_members as $user) :
				if (strstr($user->avatar, 'http') === false) {
					$user->avatar = avatar_url($user->avatar, $user->email);
				}
			?>
			{id: '<?php e($user->user_id)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php echo $user->avatar?>'},
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
</script>