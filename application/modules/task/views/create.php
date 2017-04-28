<?php
$success_conditions = [
	'action_gate' => lang('tk_action_gate'),
	'action_outcome' => lang('tk_action_outcome'),
	'implement_outcome' => lang('tk_implement_outcome'),
	'contingency_plan' => lang('tk_contingency_plan')
];

$action_types = [
	'decide' => lang('tk_decide'),
	'plan' => lang('tk_plan'),
	'prioritize' => lang('tk_prioritize'),
	'assess' => lang('tk_assess'),
	'review' => lang('tk_review')
];
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title" id="myModaloneLabel"><?php e(lang('tk_create_task')) ?></h4>
</div> <!-- end MODAL-HEADER -->

<?php echo form_open($this->uri->uri_string(), ['class' => 'form-ajax']) ?>

<div class="container-fluid modal-body">
	<?php echo mb_form_input('text', 'name', lang('tk_name'), true, set_value('name')) ?>
	<?php echo mb_form_dropdown('action_type', $action_types, set_value('action_type', ! empty($action->action_type) ? $action->action_type : null), lang('tk_action_type'), 'class="an-form-control ' . iif( form_error('action_type') , ' danger') .'"', '', true) ?>
	<?php echo mb_form_input('text', 'owner_id', lang('tk_owner'), false, set_value('owner_id', ! empty($action->owner_id) ? $action->owner_id : null), 'owner-id an-tags-input', '', lang('tk_select_team_member')) ?>
	<?php echo mb_form_input('text', 'team', lang('tk_resource'), false, set_value('team'), 'team select-member an-tags-input', '', lang('tk_add_team_member')) ?>
</div>

<div class="modal-footer">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('tk_create'))?></button>
	<a href="#" class="an-btn an-btn-primary-transparent" data-dismiss="modal"><?php e(lang('tk_cancel'))?></a>
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

	$('.owner-id').selectize({
		plugins: ['select-member'],
		persist: false,
		maxItems: 1,
		valueField: 'id',
		labelField: 'name',
		searchField: ['name'],
		options: [
			<?php foreach($project_members as $user): 
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

	$('.team').selectize({
		plugins: ['remove_button', 'select-member'],
		persist: false,
		maxItems: null,
		valueField: 'id',
		labelField: 'name',
		searchField: ['name'],
		options: [
			<?php foreach($project_members as $user): 
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