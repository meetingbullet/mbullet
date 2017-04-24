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
<?php if ($this->input->is_ajax_request()): ?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title" id="myModaloneLabel"><?php e(lang('ac_create_action'))?></h4>
</div> <!-- end MODAL-HEADER -->
<?php else: ?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
	<h2><?php e(lang('ac_create_action'))?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<?php endif; ?>

<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '']) ?>

<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
	<?php echo mb_form_input('text', 'name', lang('ac_action_name'), true) ?>
	<?php echo mb_form_dropdown('success_condition', $success_conditions, set_value('success_condition'), lang('ac_success_condition'), 'class="an-form-control ' . iif( form_error('success_condition') , ' danger') .'"', '', true) ?>
	<?php echo mb_form_dropdown('action_type', $action_types, set_value('action_type'), lang('ac_action_type'), 'class="an-form-control ' . iif( form_error('action_type') , ' danger') .'"', '', true) ?>
	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label for="owner_name" class="pull-right"><?php e(lang('ac_owner_id')) ?></label>
		</div>
		<div class="col-md-9 col-sm-12" id="owner-warpper">
			<div class="an-input-group">
				<div class="an-input-group-addon"><i class="ion-android-search"></i></div>
				<input type="text" class="an-form-control <?php echo (form_error('owner_id') || ! empty($form_error['owner_id'])) ? 'danger' : '' ?>" id="owner-name" name="owner_name" value="<?php echo set_value('owner_name') ?>" data-get-owner-url="<?php echo base_url() . 'projects/get_members/' . $project_key ?>"/>
			</div>
			<input type="hidden" class="an-form-control" id="owner-id" name="owner_id" value="<?php echo set_value('owner_id') ?>"/>
		</div>
	</div>
	<?php echo mb_form_input('text', 'point_value_defined', lang('ac_point_value_defined')) ?>
	<?php echo mb_form_input('text', 'point_used', lang('ac_point_used')) ?>
	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label for="avarage_stars" class="pull-right"><?php e(lang('ac_avarage_stars')) ?></label>
		</div>
		<div class="col-md-9 col-sm-12">
			<div class="rating">
				<input type="radio" id="star5" name="avarage_stars" value="5" <?php echo set_radio('avarage_stars', 5) ?>/><label class="full" for="star5"></label>
				<input type="radio" id="star4half" name="avarage_stars" value="4.5" <?php echo set_radio('avarage_stars', 4.5) ?>/><label class="half" for="star4half"></label>
				<input type="radio" id="star4" name="avarage_stars" value="4" <?php echo set_radio('avarage_stars', 4) ?>/><label class="full" for="star4"></label>
				<input type="radio" id="star3half" name="avarage_stars" value="3.5" <?php echo set_radio('avarage_stars', 3.5) ?>/><label class="half" for="star3half"></label>
				<input type="radio" id="star3" name="avarage_stars" value="3" <?php echo set_radio('avarage_stars', 3) ?>/><label class="full" for="star3"></label>
				<input type="radio" id="star2half" name="avarage_stars" value="2.5" <?php echo set_radio('avarage_stars', 2.5) ?>/><label class="half" for="star2half"></label>
				<input type="radio" id="star2" name="avarage_stars" value="2" <?php echo set_radio('avarage_stars', 2) ?>/><label class="full" for="star2"></label>
				<input type="radio" id="star1half" name="avarage_stars" value="1.5" <?php echo set_radio('avarage_stars', 1.5) ?>/><label class="half" for="star1half"></label>
				<input type="radio" id="star1" name="avarage_stars" value="1" <?php echo set_radio('avarage_stars', 1) ?>/><label class="full" for="star1"></label>
				<input type="radio" id="star0half" name="avarage_stars" value="0.5" <?php echo set_radio('avarage_stars', 0.5) ?>/><label class="half" for="star0half"></label>
				<input type="radio" id="star0" name="avarage_stars" value="0" <?php echo set_radio('avarage_stars', 0) ?>/><label class="full" for="star0"></label>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label for="action_members" class="pull-right"><?php e(lang('ac_resource')) ?></label>
		</div>
		<div class="col-md-9 col-sm-12" id="action-members-warpper">
			<div class="an-input-group">
				<div class="an-input-group-addon"><i class="ion-android-search"></i></div>
				<input type="text" class="an-form-control" id="action-member-name" name="action_member_name" value="<?php echo set_value('action_member_name') ?>" data-get-resource-url="<?php echo base_url() . 'projects/get_members/' . $project_key ?>"/>
			</div>
			<input type="hidden" class="an-form-control" id="action-members" name="action_members" value="<?php echo set_value('action_members', '[]') ?>"/>
			<div class="members"></div>
		</div>
	</div>
</div>

<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('ac_create'))?></button>
	<a href="#" class="an-btn an-btn-primary-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('ac_cancel'))?></a>
</div>

<?php echo form_close(); ?>