<?php
$success_conditions = [
	'action_gate' => lang('ac_action_gate'),
	'action_outcome' => lang('ac_action_outcome'),
	'implement_outcome' => lang('ac_implement_outcome'),
	'contingency_plan' => lang('ac_contingency_plan')
];

$action_types = [
	'decide' => lang('ac_decide'),
	'plan' => lang('ac_plan'),
	'prioritize' => lang('ac_prioritize'),
	'assess' => lang('ac_assess'),
	'review' => lang('ac_review')
];
?>
<?php if (IS_AJAX): ?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title" id="myModaloneLabel"><?php e(empty($action) ? lang('ac_create_action') : lang('ac_update_action'))?></h4>
</div> <!-- end MODAL-HEADER -->
<?php else: ?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
	<h2><?php e(lang('ac_create_action'))?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<?php endif; ?>

<?php echo form_open($this->uri->uri_string(), ['class' => IS_AJAX ? 'form-ajax' : '']) ?>

<div class='container-fluid<?php echo IS_AJAX ? ' modal-body' : ''?>'>
	<?php echo mb_form_input('text', 'name', lang('ac_name'), true, set_value('name', ! empty($action->name) ? $action->name : null)) ?>
	<?php echo mb_form_dropdown('success_condition', $success_conditions, set_value('success_condition', ! empty($action->success_condition) ? $action->success_condition : null), lang('ac_success_condition'), 'class="an-form-control ' . iif( form_error('success_condition') , ' danger') .'"', '', true) ?>
	<?php echo mb_form_dropdown('action_type', $action_types, set_value('action_type', ! empty($action->action_type) ? $action->action_type : null), lang('ac_action_type'), 'class="an-form-control ' . iif( form_error('action_type') , ' danger') .'"', '', true) ?>
	<?php echo mb_form_input('text', 'owner_id', lang('ac_owner'), false, set_value('owner_id', ! empty($action->owner_id) ? $action->owner_id : null), 'owner-id an-tags-input', '', lang('ac_select_team_member')) ?>
	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label for="in" class="pull-right"><?php e(lang('ac_point_value')) ?></label>
		</div>
		<div class="col-md-9 col-sm-12">
			<div class="row">
				<div class="col-md-3">
					<input type="number" name="point_value" id="point_value" class="an-form-control<?php e(iif( form_error('point_value') , ' danger')) ?>" value="<?php e(set_value('point_value', ! empty($action->point_value) ? $action->point_value : 0)) ?>" step="0.1">
				</div>
			</div>
		</div>
	</div>
	<?php echo mb_form_input('text', 'team', lang('ac_resource'), false, set_value('owner_id', ! empty($action->members) ? $action->members : ''), 'team select-member an-tags-input', '', lang('ac_add_team_member')) ?>
</div>

<div class="<?php echo IS_AJAX ? 'modal-footer' : 'container-fluid pull-right' ?>">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(empty($action) ? lang('ac_create') : lang('ac_update'))?></button>
	<a href="#" class="an-btn an-btn-primary-transparent" <?php echo IS_AJAX ? 'data-dismiss="modal"' : '' ?>><?php e(lang('ac_cancel'))?></a>
</div>

<?php echo form_close(); ?>

<?php if (IS_AJAX) {
echo '<script type="text/javascript">' . $this->load->view('create_js', [
		'project_members' => $project_members
	], true) . '</script>';
}
?>