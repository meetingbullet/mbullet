	<div class="<?php echo IS_AJAX ? '' : 'an-content-body'?>">

		<?php if (IS_AJAX): ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			<h4 class="modal-title"><?php e(lang('st_edit_meeting'))?></h4>
		</div> <!-- end MODAL-HEADER -->
		<?php else: ?>
		<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
			<div class="an-page-title">
			<h2><?php e(lang('st_edit_meeting'))?></h2>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
		<?php endif; ?>

		<?php echo form_open($this->uri->uri_string(), ['class' => IS_AJAX ? 'form-ajax' : '', 'id' => 'form-update-meeting']) ?>

		<div class='container-fluid<?php echo IS_AJAX ? ' modal-body' : ''?>'>
				<?php echo mb_form_input('text', 'name', lang('st_name'), true, $meeting->name) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_status')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-6">
								<select name="status" class="an-form-control">
									<option value='open' <?php echo set_select('status', 'open', $meeting->status == 'open') ?>><?php e(lang('st_open'))?></option>
									<option value='inprogress' <?php echo set_select('status', 'inprogress', $meeting->status == 'inprogress') ?>><?php e(lang('st_inprogress'))?></option>
									<option value='ready' <?php echo set_select('status', 'ready', $meeting->status == 'ready') ?>><?php e(lang('st_ready'))?></option>
									<option value='resolved' <?php echo set_select('status', 'resolved', $meeting->status == 'resolved') ?>><?php e(lang('st_resolved'))?></option>
								</select>
							</div>
						</div>
					</div>
				</div>

				<?php echo mb_form_input('text', 'owner_id', lang('st_owner'), true, $meeting->owner_id, 'owner-id an-tags-input', '', lang('st_select_team_member')) ?>
				<?php echo mb_form_input('text', 'team', lang('st_resource'), true, implode(',', $meeting_members), 'team select-member an-tags-input', '', lang('st_add_team_member')) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="goal" class="pull-right"><?php e(lang('st_goal')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<textarea name="goal" class="an-form-control"><?php echo set_value('goal', $meeting->goal) ?></textarea> 
					</div>
				</div>
				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="in" class="pull-right"><?php e(lang('st_in')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<div class="row">
							<div class="col-md-5">
								<?php
								$times = [
									'1' => '1 minute',
									'5' => '5 minutes',
									'10' => '10 minutes',
									'15' => '15 minutes',
									'30' => '30 minutes',
									'60' => '1 hour',
									'120' => '2 hours',
									'180' => '3 hours',
									'300' => '5 hours',
									'480' => '8 hours',
									'other' => 'Input manually',
								]
								?>
								<select id="meeting-in" class="an-form-control" name="meeting_in">
								<?php foreach ($times as $in => $label) : ?>
									<option value="<?php echo $in ?>"
										<?php if(set_value('in', $meeting->in) == $in || ($in == 'other' && ! empty(set_value('in', $meeting->in)) && ! in_array(set_value('in', $meeting->in), array_keys($times)))) echo 'selected' ?>
									><?php echo $label ?></option>
								<?php endforeach ?>
								</select>
							</div>
							<div class="col-md-5">
								<input type="number" style="display: none;" name="in" id="in" class="an-form-control<?php e(iif( form_error('in') , ' danger')) ?>" value="<?php e(set_value('in', $meeting->in)) ?>" meeting="0.1">
							</div>
							<div class="col-md-2" style="display: none;" id="in-unit">
								<?php e(lang('st_minutes'))?>
							</div>
						</div>
					</div>
				</div>
				<input type="text" style="display: none;" name="scheduled_start_time" id="meeting-scheduled-start-time" value="<?php echo set_value('scheduled_start_time', ! empty($meeting->scheduled_start_time) ? $meeting->scheduled_start_time : '') ?>">
		</div>

		<div class="<?php echo IS_AJAX ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="save" id="update-meeting" class="an-btn an-btn-primary"><?php e(lang('st_update'))?></button>
			<a href="#" class="an-btn an-btn-danger-transparent" <?php echo IS_AJAX ? 'data-dismiss="modal"' : '' ?>><?php e(lang('st_cancel'))?></a>
		</div>

		<?php echo form_close(); ?>
	</div>

	<?php if (IS_AJAX): ?>
		<script>
			var default_cost_of_time = '<?php echo $default_cost_of_time ?>';
			var default_cost_of_time_name = '<?php echo $default_cost_of_time_name ?>';

			var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
							'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

			var project_members = [
				<?php foreach($project_members as $user): 
					if (strstr($user->avatar, 'http') === false) {
						$user->avatar = avatar_url($user->avatar, $user->email);
					}

					$index = array_search($user->email, $meeting_members);
					if ($index !== false) {
						unset($meeting_members[$index]);
					}
				?>
				{
					id: '<?php e($user->user_id)?>', 
					name: '<?php e($user->first_name . ' ' . $user->last_name)?>', 
					avatar: '<?php echo $user->avatar?>', 
					cost_of_time: <?php e($user->cost_of_time)?>,
					cost_of_time_name: '<?php e($user->cost_of_time_name)?>',
					email: '<?php e($user->email)?>', 
				},
				<?php endforeach; ?>
			];

			var anonymous_members = [
				<?php foreach ($meeting_members as $anonymous_email) : ?>
				{
					email: '<?php echo $anonymous_email ?>',
					cost_of_time: default_cost_of_time,
					cost_of_time_name: default_cost_of_time_name,
					avatar: '<?php echo avatar_url(null, $anonymous_email) ?>'
				},
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

			$('#meeting-in').change(function() {
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

			$('#meeting-in').change();
		</script>
	<?php endif; ?>