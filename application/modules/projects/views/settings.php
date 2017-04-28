<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h2><?php echo lang('pj_settings') . ": {$project->name}" ?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<?php echo form_open('') ?>
<div class="col-md-6 col-md-offset-3">
	<div class="an-single-component with-shadow">
		<div class="an-component-header">
			<h6><?php echo lang('pj_params') ?></h6>
		</div>
		<div class="an-component-body">
			<div class="an-helper-block">
				<label><?php echo lang('pj_cost_unit_of_time') ?><span class="required">*</span></label>
				<div class="inline-inputs">
					<div class="row">
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('cost_of_time_1')) ? 'danger' : '') ?>" name="cost_of_time_1" value="<?php echo set_value('cost_of_time_1', empty($project->cost_of_time_1) ? '' : $project->cost_of_time_1) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('cost_of_time_2')) ? 'danger' : '') ?>" name="cost_of_time_2" value="<?php echo set_value('cost_of_time_2', empty($project->cost_of_time_2) ? '' : $project->cost_of_time_2) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('cost_of_time_3')) ? 'danger' : '') ?>" name="cost_of_time_3" value="<?php echo set_value('cost_of_time_3', empty($project->cost_of_time_3) ? '' : $project->cost_of_time_3) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('cost_of_time_4')) ? 'danger' : '') ?>" name="cost_of_time_4" value="<?php echo set_value('cost_of_time_4', empty($project->cost_of_time_4) ? '' : $project->cost_of_time_4) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('cost_of_time_5')) ? 'danger' : '') ?>" name="cost_of_time_5" value="<?php echo set_value('cost_of_time_5', empty($project->cost_of_time_5) ? '' : $project->cost_of_time_5) ?>"/>
						</div>
						<div class="basis-100 legend">
							<?php echo lang('pj_cost_of_time_legend') ?>
						</div>
					</div>
				</div>

				<label><?php echo lang('pj_value_unit_of_time') ?><span class="required">*</span></label>
				<div class="inline-inputs">
					<div class="row">
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('value_of_time_1')) ? 'danger' : '') ?>" name="value_of_time_1" value="<?php echo set_value('value_of_time_1', empty($project->value_of_time_1) ? '' : $project->value_of_time_1) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('value_of_time_2')) ? 'danger' : '') ?>" name="value_of_time_2" value="<?php echo set_value('value_of_time_2', empty($project->value_of_time_2) ? '' : $project->value_of_time_2) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('value_of_time_3')) ? 'danger' : '') ?>" name="value_of_time_3" value="<?php echo set_value('value_of_time_3', empty($project->value_of_time_3) ? '' : $project->value_of_time_3) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('value_of_time_4')) ? 'danger' : '') ?>" name="value_of_time_4" value="<?php echo set_value('value_of_time_4', empty($project->value_of_time_4) ? '' : $project->value_of_time_4) ?>"/>
						</div>
						<div class="basis-20">
							<input type="text" class="margin-0 an-form-control <?php echo (! empty(form_error('value_of_time_5')) ? 'danger' : '') ?>" name="value_of_time_5" value="<?php echo set_value('value_of_time_5', empty($project->value_of_time_5) ? '' : $project->value_of_time_5) ?>"/>
						</div>
						<div class="basis-100 legend">
							<?php echo lang('pj_value_of_time_legend') ?>
						</div>
					</div>
				</div>
			</div>
		</div> <!-- end .AN-COMPONENT-BODY -->
	</div>

	<div class="an-single-component with-shadow">
		<div class="an-component-header">
			<h6><?php echo lang('pj_unit_of_time') ?></h6>
		</div>
		<div class="an-component-body">
			<div class="an-helper-block">
				<div class="inline-inputs">
					<div class="row">
						<div class="basis-50">
							<label><?php echo lang('pj_cost') ?></label>
							<input class="an-form-control <?php echo (! empty(form_error('cost')) ? 'danger' : '') ?>" name="cost" value="<?php echo set_value('cost', empty($project->cost) ? '' : $project->cost) ?>"/>
						</div>
						<div class="basis-50">
							<label><?php echo lang('pj_value') ?></label>
							<input class="an-form-control <?php echo (! empty(form_error('value')) ? 'danger' : '') ?>" name="value" value="<?php echo set_value('value', empty($project->value) ? '' : $project->value) ?>"/>
						</div>
					</div>
				</div>
			</div>
		</div> <!-- end .AN-COMPONENT-BODY -->
	</div>

	<label><?php echo lang('pj_point_converter') ?></label>
	<div class="inline-inputs">
		<div class="row">
			<div class="col-xs-12">
				<input type="text" class="an-form-control <?php echo (! empty(form_error('point_converter')) ? 'danger' : '') ?>" name="point_converter" value="<?php echo set_value('point_converter', empty($project->point_converter) ? '' : $project->point_converter) ?>"/>
			</div>
		</div>
	</div>

	<label><?php echo lang('pj_thresholds') ?></label>
	<div class="inline-inputs">
		<div class="row">
			<div class="col-xs-12">
				<div class="an-user-lists tables messages thresholds">
					<div class="list-title">
						<h6 class="basis-40"></h6>
						<h6 class="basis-20"><?php e(lang('pj_decision')) ?></h6>
						<h6 class="basis-20"><?php e(lang('pj_step_owner')) ?></h6>
						<h6 class="basis-20"><?php e(lang('pj_contributor')) ?></h6>
					</div>

					<div class="an-lists-body an-customScrollbar ps-container ps-theme-default">
						<div class="list-user-single">
							<div class="list-name basis-40">
								<?php echo lang('pj_no_hours') ?>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('project_no_of_hour')) ? 'danger' : '') ?>" name="project_no_of_hour" value="<?php echo set_value('project_no_of_hour', empty($project->project_no_of_hour) ? '' : $project->project_no_of_hour) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('step_owner_no_of_hour')) ? 'danger' : '') ?>" name="step_owner_no_of_hour" value="<?php echo set_value('step_owner_no_of_hour', empty($project->step_owner_no_of_hour) ? '' : $project->step_owner_no_of_hour) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('contributor_no_of_hour')) ? 'danger' : '') ?>" name="contributor_no_of_hour" value="<?php echo set_value('contributor_no_of_hour', empty($project->contributor_no_of_hour) ? '' : $project->contributor_no_of_hour) ?>"/>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->

						<div class="list-user-single">
							<div class="list-name basis-40">
								<?php echo lang('pj_total_cost') ?>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('project_total_cost')) ? 'danger' : '') ?>" name="project_total_cost" value="<?php echo set_value('project_total_cost', empty($project->project_total_cost) ? '' : $project->project_total_cost) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('step_owner_total_cost')) ? 'danger' : '') ?>" name="step_owner_total_cost" value="<?php echo set_value('step_owner_total_cost', empty($project->step_owner_total_cost) ? '' : $project->step_owner_total_cost) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('contributor_total_cost')) ? 'danger' : '') ?>" name="contributor_total_cost" value="<?php echo set_value('contributor_total_cost', empty($project->contributor_total_cost) ? '' : $project->contributor_total_cost) ?>"/>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->

						<div class="list-user-single">
							<div class="list-name basis-40">
								<?php echo lang('pj_no_points') ?>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('project_no_of_point')) ? 'danger' : '') ?>" name="project_no_of_point" value="<?php echo set_value('project_no_of_point', empty($project->project_no_of_point) ? '' : $project->project_no_of_point) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('step_owner_no_of_point')) ? 'danger' : '') ?>" name="step_owner_no_of_point" value="<?php echo set_value('step_owner_no_of_point', empty($project->step_owner_no_of_point) ? '' : $project->step_owner_no_of_point) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('contributor_no_of_point')) ? 'danger' : '') ?>" name="contributor_no_of_point" value="<?php echo set_value('contributor_no_of_point', empty($project->contributor_no_of_point) ? '' : $project->contributor_no_of_point) ?>"/>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->

						<div class="list-user-single">
							<div class="list-name basis-40">
								<?php echo lang('pj_min_ratio') ?>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('project_min_ratio')) ? 'danger' : '') ?>" name="project_min_ratio" value="<?php echo set_value('project_min_ratio', empty($project->project_min_ratio) ? '' : $project->project_min_ratio) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('step_owner_min_ratio')) ? 'danger' : '') ?>" name="step_owner_min_ratio" value="<?php echo set_value('step_owner_min_ratio', empty($project->step_owner_min_ratio) ? '' : $project->step_owner_min_ratio) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('contributor_min_ratio')) ? 'danger' : '') ?>" name="contributor_min_ratio" value="<?php echo set_value('contributor_min_ratio', empty($project->contributor_min_ratio) ? '' : $project->contributor_min_ratio) ?>"/>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->

						<div class="list-user-single">
							<div class="list-name basis-40">
								<?php echo lang('pj_min_star') ?>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('project_min_star')) ? 'danger' : '') ?>" name="project_min_star" value="<?php echo set_value('project_min_star', empty($project->project_min_star) ? '' : $project->project_min_star) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('step_owner_min_star')) ? 'danger' : '') ?>" name="step_owner_min_star" value="<?php echo set_value('step_owner_min_star', empty($project->step_owner_min_star) ? '' : $project->step_owner_min_star) ?>"/>
							</div>
							<div class="list-name basis-20">
								<input type="text" class="an-form-control <?php echo (! empty(form_error('contributor_min_star')) ? 'danger' : '') ?>" name="contributor_min_star" value="<?php echo set_value('contributor_min_star', empty($project->contributor_min_star) ? '' : $project->contributor_min_star) ?>"/>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->
					</div> <!-- end .AN-LISTS-BODY -->
				</div>
			</div>
		</div>
	</div>

	<div class="pull-right">
		<button class="an-btn an-btn-success" type="submit"><?php echo lang('pj_update') ?></button>
	</div>
</div>
<?php echo form_close() ?>