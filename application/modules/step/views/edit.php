	<div class="<?php echo IS_AJAX ? '' : 'an-content-body'?>">

		<?php if (IS_AJAX): ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			<h4 class="modal-title"><?php e(lang('st_edit_step'))?></h4>
		</div> <!-- end MODAL-HEADER -->
		<?php else: ?>
		<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
			<div class="an-page-title">
			<h2><?php e(lang('st_edit_step'))?></h2>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
		<?php endif; ?>

		<?php echo form_open($this->uri->uri_string(), ['class' => IS_AJAX ? 'form-ajax' : '', 'id' => 'form-update-step']) ?>

		<div class='container-fluid<?php echo IS_AJAX ? ' modal-body' : ''?>'>
				<?php echo mb_form_input('text', 'name', lang('st_name'), true, $step->name) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_status')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-6">
								<select name="status" class="an-form-control">
									<option value='open' <?php echo set_select('status', 'open', $step->status == 'open') ?>><?php e(lang('st_open'))?></option>
									<option value='inprogress' <?php echo set_select('status', 'inprogress', $step->status == 'inprogress') ?>><?php e(lang('st_inprogress'))?></option>
									<option value='ready' <?php echo set_select('status', 'ready', $step->status == 'ready') ?>><?php e(lang('st_ready'))?></option>
									<option value='resolved' <?php echo set_select('status', 'resolved', $step->status == 'resolved') ?>><?php e(lang('st_resolved'))?></option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<?php echo mb_form_input('text', 'owner_id', lang('st_owner'), true, $step->owner_id, 'owner-id an-tags-input', '', lang('st_select_team_member')) ?>
				<?php echo mb_form_input('text', 'team', lang('st_resource'), false, implode(',', $step_members), 'team select-member an-tags-input', '', lang('st_add_team_member')) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="goal" class="pull-right"><?php e(lang('st_goal')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<textarea name="goal" class="an-form-control"><?php echo set_value('goal', $step->goal) ?></textarea> 
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_in')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-3">
								<input type="number" name="in" id="in" class="an-form-control<?php e(iif( form_error('in') , ' danger')) ?>" value="<?php e(set_value('in', $step->in)) ?>" step="0.1">
							</div>
							<div class="col-md-3">
								<?php e(lang('st_minutes'))?>
							</div>
						</div>
					</div>
				</div>
		</div>

		<div class="<?php echo IS_AJAX ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="save" id="update-step" class="an-btn an-btn-primary"><?php e(lang('st_update'))?></button>
			<a href="#" class="an-btn an-btn-danger-transparent" <?php echo IS_AJAX ? 'data-dismiss="modal"' : '' ?>><?php e(lang('st_cancel'))?></a>
		</div>

		<?php echo form_close(); ?>
	</div>

	<?php if (IS_AJAX): ?>
		<script>
			var project_members = [
				<?php foreach($project_members as $user): 
					if (strstr($user->avatar, 'http') === false) {
						$user->avatar = avatar_url($user->avatar, $user->email);
					}
				?>
				{id: '<?php e($user->user_id)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php echo $user->avatar?>', cost_of_time: <?php echo $user->cost_of_time?>},
				<?php endforeach; ?>
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
		</script>
	<?php endif; ?>