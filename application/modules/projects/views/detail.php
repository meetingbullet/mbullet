<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
		<h2><?php e($project_name)?>
			<button type="button" class="an-btn an-btn-icon small dropdown-toggle setting btn-prj-more" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
				<i class="ion-ios-more-outline"></i>
			</button>
			<div class="dropdown-menu right-align">
				<ul class="an-basic-list">
				<li><a href="#"><? e(lang('pj_more_btn_open_pj')) ?></a></li>
				<li><a href="#"><? e(lang('pj_more_btn_inactive_pj')) ?></a></li>
				<li><a href="#"><? e(lang('pj_more_btn_archive_pj')) ?></a></li>
				<li role="separator" class="divider"></li>
				<li><a href="#"><? e(lang('pj_more_btn_pj_setting')) ?></a></li>
				</ul>
			</div>
		</h2>
	</div>
	<a id="back-btn" href="/dashboard" class="an-btn an-btn-primary-transparent"><?php echo lang('pj_back') ?></a>
</div> <!-- end AN-BODY-TOPBAR -->
<div class='row'>
	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php echo lang('pj_detail') ?></h6>
			</div>
			<div class="an-component-body pj-detail">
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_project_name') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['project']->name) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_cost_code') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['project']->cost_code) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_owner') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><img style="width: 24px; height: auto;" src="<?php echo avatar_url($detail['project']->avatar, $detail['project']->email) ?>" class="img-circle"/> <?php e($detail['project']->full_name) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_detail_tab_info_table_label_status') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e(str_replace('-', ' ', $detail['project']->status)) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_created_on') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['project']->created_on) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_mofified_on') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['project']->modified_on) ?></div>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>

	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php echo lang('pj_constraint') ?></h6>
			</div>
			<div class="an-component-body pj-detail">
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_min') . ' ' . lang('pj_hours') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->min_hour) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_max') . ' ' . lang('pj_hours') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->max_hour) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_no_of_meetings') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->no_meeting) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_no_of_attendees') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->no_attendee) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_min') . ' ' . lang('pj_roi_rating') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->min_roi_rating) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_min') . ' ' . lang('pj_roi_rating') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->max_roi_rating) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_min') . ' ' . lang('pj_period') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->min_period . ' ' . $detail['constraint']->period_type) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_min') . ' ' . lang('pj_period') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['constraint']->max_period . ' ' . $detail['constraint']->period_type) ?></div>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>
	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php echo lang('pj_expectation') ?></h6>
			</div>
			<div class="an-component-body pj-detail">
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_min') . ' ' . lang('pj_hours') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['expectation']->min_hour) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_max') . ' ' . lang('pj_hours') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['expectation']->max_hour) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_no_of_meetings') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['expectation']->no_meeting) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_no_of_attendees') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['expectation']->no_attendee) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_time_cost_to_milestone') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['expectation']->time_cost_to_milestone . ' ' . $detail['expectation']->period_type) ?></div>
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_return_on_invested_hours') . ':' ?></div>
				<div class="col-md-6 pj-detail-item"><?php e($detail['expectation']->outcomes_per_period . ' ' . $detail['expectation']->period_type) ?></div>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>
	<div class="col-md-3">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php echo lang('pj_config') ?></h6>
			</div>
			<div class="an-component-body pj-detail">
				<div class="col-md-6 pj-detail-item"><?php echo lang('pj_cost_unit_time') . ':' ?></div>
				<div class="col-md-6 pj-detail-item">
					<?php e($detail['project']->cost_of_time_1 . ' = ' . $detail['project']->value_of_time_1) ?> <br/>
					<?php e($detail['project']->cost_of_time_2 . ' = ' . $detail['project']->value_of_time_2) ?> <br/>
					<?php e($detail['project']->cost_of_time_3 . ' = ' . $detail['project']->value_of_time_3) ?> <br/>
					<?php e($detail['project']->cost_of_time_4 . ' = ' . $detail['project']->value_of_time_4) ?> <br/>
					<?php e($detail['project']->cost_of_time_5 . ' = ' . $detail['project']->value_of_time_5) ?>
				</div>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>

	<div class="col-md-12">
		<!--div class="an-single-component with-shadow"-->
			<!--div class="an-component-body"-->
				<!--div class="an-bootstrap-custom-tab"-->
					<!--div class="an-tab-control"-->
						<!-- Nav tabs -->
						<!--ul class="nav nav-tabs text-left" role="tablist">
							<li role="presentation" class="active"><a href="#info" aria-controls="info" role="tab" data-toggle="tab"><?php //e(lang('pj_detail_tab_info')) ?></a>
							</li>
							<li role="presentation"><a href="#action" aria-controls="action" role="tab" data-toggle="tab"><?php //e(lang('pj_detail_tab_action')) ?></a>
							</li>
							<li role="presentation"><a href="#report" aria-controls="report" role="tab" data-toggle="tab"><?php //e(lang('pj_detail_tab_report')) ?></a>
							</li>
						</ul-->
					<!--/div-->

					<!-- Tab panes -->
					<!--div class="tab-content"-->
						<!--div role="tabpanel" class="tab-pane fade in active" id="info">
							<?php //$this->load->view('info', $info_tab_data) ?>
						</div--> <!-- end .TAB-PANE -->

						<!--div role="tabpanel" class="tab-pane fade in" id="action">
							<?php //$this->load->view('action', $action_tab_data) ?>
						</div--> <!-- end .TAB-PANE -->

						<!--div role="tabpanel" class="tab-pane fade in" id="report">
							<?php //$this->load->view('report', $report_tab_data) ?>
						</div--> <!-- end .TAB-PANE -->
					<!--/div--> <!-- end .TAB-CONTENT -->
				<!--/div!--> <!-- end .AN-BOOTSTRAP-CUSTOM-TAB -->
			<!--/div--> <!-- end .AN-COMPONENT-BODY -->
		<!--/div-->
		<?php
		/****** temporary disable tab *******/
		$this->load->view('info', $info_tab_data)
		?>
	</div>
</div>
<!-- Modal -->
<div class="modal fade" id="bigModal" tabindex="-1" role="dialog" aria-labelledby="bigModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>