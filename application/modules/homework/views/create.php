<?php
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title"><?php e(lang('hw_add_homework')) ?></h4>
</div> <!-- end MODAL-HEADER -->

<?php echo form_open($this->uri->uri_string(), ['class' => 'form-ajax', 'id' => 'create-homework']) ?>

<div class="container-fluid modal-body">
	<?php echo mb_form_input('text', 'name', lang('hw_name'), true, null) ?>
	<?php echo mb_form_input('text', 'description', lang('hw_description'), false, null) ?>
	<?php echo mb_form_input('number', 'time_spent', lang('hw_time_spent'), true, null, 'an-form-control', null, null, null, 'meeting=".01"') ?>
	<?php echo mb_form_input('text', 'member', lang('hw_member'), true, null, 'team select-member an-tags-input', '', lang('hw_add_team_member')) ?>
</div>

<div class="modal-footer">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('hw_add'))?></button>
	<a href="#" class="an-btn an-btn-primary-transparent" data-dismiss="modal"><?php e(lang('hw_cancel'))?></a>
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