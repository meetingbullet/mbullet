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
											<div class="col-md-6">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
													<input type="number" name="contraints[min_hour]" class="an-form-control col-md-6"/>
												</div>
											</div>
											<div class="col-md-6">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
													<input type="number" name="contraints[max_hour]" class="an-form-control col-md-6"/>
												</div>
											</div>
										</div>
									</div>
								</div>

								<?php echo mb_form_input('text', 'contraints[no_meeting]', lang('pj_no_of_meetings')) ?>
								<?php echo mb_form_input('text', 'contraints[no_atendee]', lang('pj_no_of_atendees')) ?>
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
											<div class="col-md-6">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_min'))?></span></div>
													<input type="number" name="expectations[min_hour]" class="an-form-control col-md-6"/>
												</div>
											</div>
											<div class="col-md-6">
												<div class="an-input-group">
													<div class="an-input-group-addon"><span><?php e(lang('pj_max'))?></span></div>
													<input type="number" name="expectations[max_hour]" class="an-form-control col-md-6"/>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php echo mb_form_input('text', 'expectations[no_meeting]', lang('pj_no_of_meetings')) ?>
								<?php echo mb_form_input('text', 'expectations[no_atendee]', lang('pj_no_of_atendees')) ?>
								<?php echo mb_form_input('text', 'expectations[time_cost_to_milestone]', lang('pj_time_cost_to_milestone'), '' , 'an-form-control col-md-3') ?>

								<div class="row">
								<div class="col-md-3 col-sm-12">
									<label for="expectations[outcomes_per_period]" class="pull-right"><?php e(lang('outcomes_per_period'))?></label>
								</div>
								<div class="col-md-9 col-sm-12">
									<div class="an-input-group-right">
										<input type="text" name="expectation[outcomes_per_period]" class="an-form-control col-md-3">
										<select name="expectation[peroid_type]" class="an-form-control col-md-3">
											<option value='hour'><?php e(lang('pj_hours'))?></option>
											<option value='day'><?php e(lang('pj_days'))?></option>
											<option value='week'><?php e(lang('pj_weeks'))?></option>
										</select>
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