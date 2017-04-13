	<div class="an-content-body">
		<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
			<div class="an-page-title">
			<h2><?php e(lang('pj_create_project'))?></h2>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
		<div class='row'>
			<div class="col-md-12">
				<?php echo form_open() ?>

					<?php echo mb_form_input('text', 'name', lang('pj_project_name')) ?>

					<div class="an-single-component with-shadow">
						<div class="an-component-header">
							<h6><?php e(lang('pj_constraints'))?></h6>
						</div>

						<div class="an-component-body">
							<div class="an-helper-block">
								<div class="row">
									<div class="col-md-3 col-sm-12">
										<label for="no_meeting" class="pull-right"><?php e(lang('pj_investment_hours'))?></label>
									</div>
									<div class="col-md-9 col-sm-12">
										<div class="container-fluid row">
											<div class="col-md-3">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
													<input type="number" name="contraints[min_hour]" class="an-form-control<?php e(iif( form_error('contraints[min_hour]') , ' danger')) ?>" value="<?php e(set_value('contraints[min_hour]')) ?>"/>
												</div>
											</div>
											<div class="col-md-3">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
													<input type="number" name="contraints[max_hour]" class="an-form-control<?php e(iif( form_error('contraints[max_hour]') , ' danger')) ?>" value="<?php e(set_value('contraints[max_hour]')) ?>"/>
												</div>
											</div>
										</div>
									</div>
								</div>

								<?php echo mb_form_input('number', 'contraints[no_meeting]', lang('pj_no_of_meetings')) ?>
								<?php echo mb_form_input('number', 'contraints[no_attendee]', lang('pj_no_of_attendees')) ?>

								<div class="row">
									<div class="col-md-3 col-sm-12">
										<label for="no_meeting" class="pull-right"><?php e(lang('pj_roi_rating'))?></label>
									</div>
									<div class="col-md-9 col-sm-12">
										<div class="container-fluid row">
											<div class="col-md-3">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
													<input type="number" name="contraints[min_roi_rating]" class="an-form-control<?php e(iif( form_error('contraints[min_roi_rating]') , ' danger')) ?>" value="<?php e(set_value('contraints[min_roi_rating]')) ?>"/>
												</div>
											</div>
											<div class="col-md-3">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
													<input type="number" name="contraints[max_roi_rating]" class="an-form-control<?php e(iif( form_error('contraints[max_roi_rating]') , ' danger')) ?>" value="<?php e(set_value('contraints[max_roi_rating]')) ?>"/>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-3 col-sm-12">
										<label for="no_meeting" class="pull-right"><?php e(lang('pj_period'))?></label>
									</div>
									<div class="col-md-9 col-sm-12">
										<div class="container-fluid row">
											<div class="col-md-3">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
													<input type="number" name="contraints[min_period]" class="an-form-control<?php e(iif( form_error('contraints[min_period]') , ' danger')) ?>" value="<?php e(set_value('contraints[min_period]')) ?>"/>
												</div>
											</div>
											<div class="col-md-3">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
													<input type="number" name="contraints[max_peroid]" class="an-form-control<?php e(iif( form_error('contraints[max_peroid]') , ' danger')) ?>" value="<?php e(set_value('contraints[max_peroid]')) ?>"/>
												</div>
											</div>
											<div class="col-md-2">
												<select name="contraints[peroid_type]" class="an-form-control">
													<option value='hours' <?php echo set_select('contraints[peroid_type]', 'hours') ?>><?php e(lang('pj_hours'))?></option>
													<option value='days' <?php echo set_select('contraints[peroid_type]', 'days') ?>><?php e(lang('pj_days'))?></option>
													<option value='weeks' <?php echo set_select('contraints[peroid_type]', 'weeks') ?>><?php e(lang('pj_weeks'))?></option>
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
										<label for="no_meeting" class="pull-right"><?php e(lang('pj_return_on_invested_hours'))?></label>
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

								<?php echo mb_form_input('number', 'expectations[no_meeting]', lang('pj_no_of_meetings')) ?>
								<?php echo mb_form_input('number', 'expectations[no_attendee]', lang('pj_no_of_attendees')) ?>

								<div class="row">
									<div class="col-md-3 col-sm-12">
										<label for="expectations[outcomes_per_period]" class="pull-right"><?php e(lang('pj_time_cost_to_milestone'))?></label>
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
										<label for="expectations[outcomes_per_period]" class="pull-right"><?php e(lang('pj_outcomes_per_period'))?></label>
									</div>
									<div class="col-md-9 col-sm-12">
										<div class="container-fluid row">
											<div class="col-md-3">
												<input type="number" name="expectations[outcomes_per_period]" class="an-form-control<?php e(iif( form_error('expectations[outcomes_per_period]') , ' danger')) ?>" value="<?php e(set_value('expectations[outcomes_per_period]')) ?>">
											</div>

											<div class="col-md-2">
												<select name="expectations[peroid_type]" class="an-form-control">
													<option value='hours' <?php echo set_select('expectations[peroid_type]', 'hours') ?>><?php e(lang('pj_hours'))?></option>
													<option value='days' <?php echo set_select('expectations[peroid_type]', 'days') ?>><?php e(lang('pj_days'))?></option>
													<option value='weeks' <?php echo set_select('expectations[peroid_type]', 'weeks') ?>><?php e(lang('pj_weeks'))?></option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div> <!-- end .AN-COMPONENT-BODY -->
					</div>

					<?php echo mb_form_input('text', 'cost_code', lang('pj_cost_code'), '', 'an-form-control', '', lang('pj_ex_pjn')) ?>
					<?php echo mb_form_input('text', 'invite_team', lang('pj_invite_team'), '', 'an-tags-input js-input-tags', '', lang('pj_member_email')) ?>

					<div class="row">
						<div class="col-md-3 col-sm-12">
							<label for="goal" class="pull-right"><?php e(lang('pj_goal')) ?></label>
						</div>
						<div class="col-md-9 col-sm-12">
							<textarea name="goal" class="an-form-control"><?php echo set_value('goal') ?></textarea> 
						</div>
					</div>

					<div class="pull-right">
						<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('pj_create'))?></button>
						<a href="" class="an-btn an-btn-primary-transparent"><?php e(lang('pj_cancel'))?></a>
					</div>
				<?php echo form_close(); ?>
			</div>
		</div>
	</div>