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
</div> <!-- end AN-BODY-TOPBAR -->
<div class='row'>
	<div class="col-md-12">
		<div class="an-single-component with-shadow">
			<div class="an-component-body">
				<div class="an-bootstrap-custom-tab">
					<div class="an-tab-control">
						<!-- Nav tabs -->
						<ul class="nav nav-tabs text-left" role="tablist">
							<li role="presentation" class="active"><a href="#info" aria-controls="info" role="tab" data-toggle="tab"><?php e(lang('pj_detail_tab_info')) ?></a>
							</li>
							<li role="presentation"><a href="#action" aria-controls="action" role="tab" data-toggle="tab"><?php e(lang('pj_detail_tab_action')) ?></a>
							</li>
							<li role="presentation"><a href="#report" aria-controls="report" role="tab" data-toggle="tab"><?php e(lang('pj_detail_tab_report')) ?></a>
							</li>
						</ul>
					</div>

					<!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane fade in active" id="info">
							<?php $this->load->view('info', $info_tab_data) ?>
						</div> <!-- end .TAB-PANE -->

						<div role="tabpanel" class="tab-pane fade in" id="action">
							<?php $this->load->view('action', $action_tab_data) ?>
						</div> <!-- end .TAB-PANE -->

						<div role="tabpanel" class="tab-pane fade in" id="report">
							<?php $this->load->view('report', $report_tab_data) ?>
						</div> <!-- end .TAB-PANE -->
					</div> <!-- end .TAB-CONTENT -->
				</div> <!-- end .AN-BOOTSTRAP-CUSTOM-TAB -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>
	</div>
</div>