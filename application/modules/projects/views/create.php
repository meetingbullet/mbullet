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

		<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
			<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '']) ?>

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
									<div class="container-fluid row">
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
												<input type="number" name="constraints[min_hour]" class="an-form-control<?php e(iif( form_error('constraints[min_hour]') , ' danger')) ?>" value="<?php e(set_value('constraints[min_hour]')) ?>"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
												<input type="number" name="constraints[max_hour]" class="an-form-control<?php e(iif( form_error('constraints[max_hour]') , ' danger')) ?>" value="<?php e(set_value('constraints[max_hour]')) ?>"/>
											</div>
										</div>
									</div>
								</div>
							</div>

							<?php echo mb_form_input('number', 'constraints[no_meeting]', lang('pj_no_of_meetings'), true) ?>
							<?php echo mb_form_input('number', 'constraints[no_attendee]', lang('pj_no_of_attendees'), true) ?>

							<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="no_meeting" class="pull-right"><?php e(lang('pj_roi_rating'))?><span class="required">*</span></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="container-fluid row">
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
									<div class="container-fluid row">
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
												<input type="number" name="constraints[min_period]" class="an-form-control<?php e(iif( form_error('constraints[min_period]') , ' danger')) ?>" value="<?php e(set_value('constraints[min_period]')) ?>"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
												<input type="number" name="constraints[max_period]" class="an-form-control<?php e(iif( form_error('constraints[max_period]') , ' danger')) ?>" value="<?php e(set_value('constraints[max_period]')) ?>"/>
											</div>
										</div>
										<div class="col-md-2">
											<select name="constraints[period_type]" class="an-form-control">
												<option value='hours' <?php echo set_select('constraints[period_type]', 'hours') ?>><?php e(lang('pj_hours'))?></option>
												<option value='days' <?php echo set_select('constraints[period_type]', 'days') ?>><?php e(lang('pj_days'))?></option>
												<option value='weeks' <?php echo set_select('constraints[period_type]', 'weeks') ?>><?php e(lang('pj_weeks'))?></option>
											</select>
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
									<div class="container-fluid row">
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
												<input type="number" name="expectations[min_hour]" class="an-form-control<?php e(iif( form_error('expectations[min_hour]') , ' danger')) ?>" value="<?php e(set_value('expectations[min_hour]')) ?>"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="an-input-group">
												<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
												<input type="number" name="expectations[max_hour]" class="an-form-control<?php e(iif( form_error('expectations[max_hour]') , ' danger')) ?>" value="<?php e(set_value('expectations[max_hour]')) ?>"/>
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
									<div class="container-fluid row">
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
									<div class="container-fluid row">
										<div class="col-md-3">
											<input type="number" name="expectations[outcomes_per_period]" class="an-form-control<?php e(iif( form_error('expectations[outcomes_per_period]') , ' danger')) ?>" value="<?php e(set_value('expectations[outcomes_per_period]')) ?>">
										</div>

										<div class="col-md-2">
											<select name="expectations[period_type]" class="an-form-control">
												<option value='hours' <?php echo set_select('expectations[period_type]', 'hours') ?>><?php e(lang('pj_hours'))?></option>
												<option value='days' <?php echo set_select('expectations[period_type]', 'days') ?>><?php e(lang('pj_days'))?></option>
												<option value='weeks' <?php echo set_select('expectations[period_type]', 'weeks') ?>><?php e(lang('pj_weeks'))?></option>
											</select>
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

				
			<?php echo form_close(); ?>
		</div>

		<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('pj_create'))?></button>
			<a href="#" class="an-btn an-btn-primary-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('pj_cancel'))?></a>
		</div>
	</div>