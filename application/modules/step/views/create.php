	<div class="<?php echo $this->input->is_ajax_request() ? '' : 'an-content-body'?>">

		<?php if ($this->input->is_ajax_request()): ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			<h4 class="modal-title"><?php e(lang('st_create_step'))?></h4>
		</div> <!-- end MODAL-HEADER -->
		<?php else: ?>
		<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
			<div class="an-page-title">
			<h2><?php e(lang('st_create_step'))?></h2>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
		<?php endif; ?>

		<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '']) ?>

		<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
				<?php echo mb_form_input('text', 'name', lang('st_name'), true) ?>
				<?php echo mb_form_input('text', 'owner_id', lang('st_owner'), true, '', 'owner-id an-tags-input', '', lang('st_select_team_member')) ?>
				<?php echo mb_form_input('text', 'team', lang('st_resource'), false, '', 'team select-member an-tags-input', '', lang('st_add_team_member')) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="goal" class="pull-right"><?php e(lang('st_goal')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<textarea name="goal" class="an-form-control"><?php echo set_value('goal') ?></textarea> 
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_in')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-3">
								<input type="number" name="in" id="in" class="an-form-control<?php e(iif( form_error('in') , ' danger')) ?>" value="<?php e(set_value('in', 0)) ?>" step="0.1">
							</div>
							<div class="col-md-3">
								<?php e(lang('st_hours'))?>
							</div>
						</div>
					</div>
				</div>
		</div>

		<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('st_create'))?></button>
			<a href="#" class="an-btn an-btn-danger-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('st_cancel'))?></a>
		</div>

		<?php echo form_close(); ?>
	</div>

	<?php if ($this->input->is_ajax_request()): ?>
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
		</script>
	<?php endif; ?>