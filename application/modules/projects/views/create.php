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

		<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '']) ?>

		<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
				<?php echo mb_form_input('text', 'name', lang('pj_project_name'), true) ?>

				<div class="an-single-component with-shadow">
					<div class="an-component-header">
						<h6><?php e(lang('pj_constraints'))?></h6>
					</div>

					<div class="an-component-body">
						<div class="an-helper-block">
							<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="no_meeting" class="pull-right"><?php e(lang('pj_investment_hours'))?><span class="required">*</span></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="row">
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
												<input type="number" name="constraints[min_hour]" class="an-form-control<?php e(iif( form_error('constraints[min_hour]') , ' danger')) ?>" value="<?php e(set_value('constraints[min_hour]')) ?>" step="0.1"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
												<input type="number" name="constraints[max_hour]" class="an-form-control<?php e(iif( form_error('constraints[max_hour]') , ' danger')) ?>" value="<?php e(set_value('constraints[max_hour]')) ?>" step="0.1"/>
											</div>
										</div>
									</div>
								</div>
							</div>

							<?php echo mb_form_input('number', 'constraints[no_meeting]', lang('pj_no_of_meetings'), true) ?>
							<?php echo mb_form_input('number', 'constraints[no_attendee]', lang('pj_no_of_attendees'), true) ?>
							<?php echo mb_form_input('number', 'constraints[total_point_project]', lang('pj_total_point_project'), true) ?>
							<?php echo mb_form_input('number', 'constraints[total_point_action]', lang('pj_total_point_action'), true) ?>
							<?php echo mb_form_input('number', 'constraints[total_point_resource]', lang('pj_total_point_resource'), true) ?>
							<?php echo mb_form_input('number', 'constraints[min_value_cost_ratio_per_step]', lang('pj_min_value_cost_ratio_per_step'), true) ?>
							<?php echo mb_form_input('number', 'constraints[max_time_action]', lang('pj_max_time_action'), true) ?>

							<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="no_meeting" class="pull-right"><?php e(lang('pj_roi_rating'))?><span class="required">*</span></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="row">
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
												<input type="number" name="constraints[min_roi_rating]" class="an-form-control<?php e(iif( form_error('constraints[min_roi_rating]') , ' danger')) ?>" value="<?php e(set_value('constraints[min_roi_rating]')) ?>"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
												<input type="number" name="constraints[max_roi_rating]" class="an-form-control<?php e(iif( form_error('constraints[max_roi_rating]') , ' danger')) ?>" value="<?php e(set_value('constraints[max_roi_rating]')) ?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="no_meeting" class="pull-right"><?php e(lang('pj_period'))?><span class="required">*</span></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="row">
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
												<input type="number" name="constraints[min_period]" class="an-form-control<?php e(iif( form_error('constraints[min_period]') , ' danger')) ?>" value="<?php e(set_value('constraints[min_period]')) ?>" step="0.1"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
												<input type="number" name="constraints[max_period]" class="an-form-control<?php e(iif( form_error('constraints[max_period]') , ' danger')) ?>" value="<?php e(set_value('constraints[max_period]')) ?>" step="0.1"/>
											</div>
										</div>
										<div class="col-md-3">
											<?php e(lang('pj_hours'))?>
											<!--<select name="constraints[period_type]" class="an-form-control">
												<option value='hours' <?php echo set_select('constraints[period_type]', 'hours') ?>><?php e(lang('pj_hours'))?></option>
												<option value='days' <?php echo set_select('constraints[period_type]', 'days') ?>><?php e(lang('pj_days'))?></option>
												<option value='weeks' <?php echo set_select('constraints[period_type]', 'weeks') ?>><?php e(lang('pj_weeks'))?></option>
											</select>-->
										</div>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div>

				<div class="an-single-component with-shadow">
					<div class="an-component-header">
						<h6><?php e(lang('pj_expectations'))?></h6>
					</div>

					<div class="an-component-body">
						<div class="an-helper-block">
							<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="no_meeting" class="pull-right"><?php e(lang('pj_return_on_invested_hours'))?><span class="required">*</span></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="row">
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
												<input type="number" name="expectations[min_hour]" class="an-form-control<?php e(iif( form_error('expectations[min_hour]') , ' danger')) ?>" value="<?php e(set_value('expectations[min_hour]')) ?>" step="0.1"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
												<input type="number" name="expectations[max_hour]" class="an-form-control<?php e(iif( form_error('expectations[max_hour]') , ' danger')) ?>" value="<?php e(set_value('expectations[max_hour]')) ?>" step="0.1"/>
											</div>
										</div>
									</div>
								</div>
							</div>

							<?php echo mb_form_input('number', 'expectations[no_meeting]', lang('pj_no_of_meetings'), true) ?>
							<?php echo mb_form_input('number', 'expectations[no_attendee]', lang('pj_no_of_attendees'), true) ?>

							<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="expectations[outcomes_per_period]" class="pull-right"><?php e(lang('pj_time_cost_to_milestone'))?><span class="required">*</span></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="row">
										<div class="col-md-3">
											<input type="number" name="expectations[time_cost_to_milestone]" class="an-form-control<?php e(iif( form_error('expectations[time_cost_to_milestone]') , ' danger')) ?>" value="<?php e(set_value('expectations[time_cost_to_milestone]')) ?>">
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="expectations[outcomes_per_period]" class="pull-right"><?php e(lang('pj_outcomes_per_period'))?><span class="required">*</span></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="row">
										<div class="col-md-3">
											<input type="number" name="expectations[outcomes_per_period]" class="an-form-control<?php e(iif( form_error('expectations[outcomes_per_period]') , ' danger')) ?>" value="<?php e(set_value('expectations[outcomes_per_period]')) ?>" step="0.1">
										</div>

										<div class="col-md-3">
											<?php e(lang('pj_hours'))?>
											<!--<select name="expectations[period_type]" class="an-form-control">
												<option value='hours' <?php echo set_select('expectations[period_type]', 'hours') ?>><?php e(lang('pj_hours'))?></option>
												<option value='days' <?php echo set_select('expectations[period_type]', 'days') ?>><?php e(lang('pj_days'))?></option>
												<option value='weeks' <?php echo set_select('expectations[period_type]', 'weeks') ?>><?php e(lang('pj_weeks'))?></option>
											</select>-->
										</div>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div>

				<?php echo mb_form_input('text', 'cost_code', lang('pj_cost_code'), true, '', 'an-form-control auto-cost-code', '', lang('pj_ex_pjn')) ?>
				<?php echo mb_form_input('text', 'invite_team', lang('pj_invite_team'), false, '', 'an-tags-input js-input-tags', '', lang('pj_member_email')) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="goal" class="pull-right"><?php e(lang('pj_goal')) ?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<textarea name="goal" class="an-form-control"><?php echo set_value('goal') ?></textarea> 
					</div>
				</div>

				
		</div>

		<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
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
					<?php foreach($invite_emails as $user): 
						if (strstr($user->avatar, 'http') === false) {
							$user->avatar = img_path() . 'users/' . $user->avatar;
						}
					?>
					{email: '<?php e($user->email)?>', name: '<?php e($user->first_name . ' ' . $user->last_name)?>', avatar: '<?php e($user->avatar)?>'},
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
	<?php endif; ?>