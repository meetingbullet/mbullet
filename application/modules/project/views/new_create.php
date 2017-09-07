<div class="<?php echo $this->input->is_ajax_request() ? '' : 'an-content-body'?>">

		<?php if ($this->input->is_ajax_request()): ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			<h4 class="modal-title" id="myModaloneLabel"><?php e(lang('pj_create_project'))?></h4>
		</div> <!-- end MODAL-HEADER -->
		<?php else: ?>
		<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
			<div class="an-page-title">
			<h2><?php e(lang('pj_create_project'))?></h2>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
		<?php endif; ?>

		<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '', 'id' => 'create-project']) ?>

		<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
			<?php echo mb_form_input('text', 'name', lang('pj_project_name'), true, set_value('name', empty($draft) || empty($draft->name) ? '' : $draft->name)) ?>
			<?php echo mb_form_input('text', 'cost_code', lang('pj_cost_code'), true, set_value('cost_code', empty($draft) || empty($draft->cost_code) ? '' : $draft->cost_code), 'an-form-control auto-cost-code', '', lang('pj_ex_pjn')) ?>
			<?php echo mb_form_input('text', 'deadline', lang('pj_project_deadline'), false, set_value('deadline', empty($draft) || empty($draft->deadline) ? '' : display_time($draft->deadline, null, 'Y-m-d H:i:s')), 'an-form-control', '', '', '', 'id="project-deadline"') ?>
			<?php echo mb_form_input('number', 'team_point', lang('pj_project_cost'), false, set_value('team_point', empty($draft) || empty($draft->team_point) ? '' : $draft->team_point), 'an-form-control', '', lang('pj_project_team_hours'), '', 'step="0.01" min="0"') ?>
			<?php echo mb_form_input('text', 'invite_team', lang('pj_invite_team'), false, set_value('invite_team', empty($draft) || empty($draft->invite_team) ? '' : $draft->invite_team), 'an-tags-input js-input-tags', '', lang('pj_member_email')) ?>

			<div class="row">
				<div class="col-md-3 col-sm-12">
					<label for="goal" class="pull-right"><?php e(lang('pj_goal')) ?></label>
				</div>
				<div class="col-md-9 col-sm-12">
					<textarea name="goal" class="an-form-control"><?php echo set_value('goal', empty($draft) || empty($draft->goal) ? '' : $draft->goal) ?></textarea> 
				</div>
			</div>
		</div>

		<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="save-draft" class="an-btn an-btn-danger"><?php e(lang('pj_save_draft'))?></button>
			<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('pj_create'))?></button>
			<a href="#" class="an-btn an-btn-primary-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('pj_cancel'))?></a>
		</div>

		<?php echo form_close(); ?>
	</div>

	<?php if ($this->input->is_ajax_request()): ?>
		<script>
			var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
							'(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

			$('.js-input-tags').selectize({
				persist: false,
				maxItems: null,
				valueField: 'email',
				labelField: 'name',
				searchField: ['name', 'email'],
				options: [
					<?php $temp_emails = [];
					if (! empty($draft) && ! empty($draft->members)) {
						$temp_emails = json_decode($draft->members);
					}

					foreach($invite_emails as $user): 
						if (strstr($user->avatar, 'http') === false) {
							$user->avatar = avatar_url($user->avatar, $user->email);
						}

						if (! empty($temp_emails)) {
							$index = array_search($user->email, $temp_emails);
							if ($index !== false) {
								unset($temp_emails[$index]);
							}
						}
					?>
					{email: '<?php e($user->email)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php echo $user->avatar?>'},
					<?php endforeach; ?>
					<?php foreach($temp_emails as $email) : ?>
					{email: '<?php e($email)?>', name: '', avatar: '<?php echo gravatar_url($email, 48)?>'},
					<?php endforeach ?>
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

			var selected_date = $('#project-deadline').val();
			if (selected_date) {
				$('#project-deadline').datetimepicker({
					sideBySide: true,
					minDate: new Date()
				});

				$('#project-deadline').data("DateTimePicker").date(new Date(selected_date));
			} else {
				$('#project-deadline').datetimepicker({
					sideBySide: true,
					minDate: new Date()
				});
			}
		</script>
	<?php endif; ?>