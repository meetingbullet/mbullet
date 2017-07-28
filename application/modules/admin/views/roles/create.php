<?php
?>
<?php if (IS_AJAX): ?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">×</span>
	</button>
	<h4 class="modal-title"><?php echo empty($role) ? lang('rl_create_role') : lang('rl_edit_role') . ': <strong>' . $role->name . '</strong>' ?></h4>
</div> <!-- end MODAL-HEADER -->
<?php else: ?>
<div class="an-body-topbar wow fadeIn">
	<div class="an-page-title">
	<h2><?php e(lang('rl_create_role'))?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<?php endif; ?>

<?php echo form_open($this->uri->uri_string(), ['class' => IS_AJAX ? 'form-ajax' : '', 'id' => isset($role) ? 'form-update-role' : 'form-create-role']) ?>

<div class='container-fluid<?php echo IS_AJAX ? ' modal-body' : ''?>'>
	<?php echo mb_form_input('text', 'name', lang('rl_name'), true, isset($role) ? $role->name : null, 'an-form-control', null, lang('rl_ex_administrator')) ?>
	
	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label for="description" class="pull-right"><?php echo lang('rl_description') ?></label>
		</div>
		<div class="col-md-9 col-sm-12">
			<textarea 	rows="5" 
						id="description"
						name="description" 
						class="an-form-control<?php echo iif( form_error('description') , ' danger') ?>"
						placeholder="<?php echo lang('rl_max_255_character') ?>"
			><?php echo set_value('description', isset($role) ? $role->description : '') ?></textarea>
		</div>
	</div>

	<?php if (isset($role) && $role->join_default == 0 || !isset($role) ): ?>
	<div class="row">
		<div class="col-md-3 col-sm-12">
		</div>
		<div class="col-md-9 col-sm-12">
			<span class="an-custom-checkbox primary blocked">
				<input type="checkbox" id="join_default" name="join_default" <?php echo isset($role) && $role->join_default ? 'checked="checked"' : false ?>>
				<label for="join_default"><?php echo lang('rl_join_default') ?> <span class="text-muted"><?php echo lang('rl_an_user_will_be_joint_to_this_role_after_accepting_an_invitation') ?></span></label>
			</span>
		</div>
	</div>
	<?php else: ?>
		<p class="an-small-doc-block">
			<?php echo lang('rl_invited_user_will_be_automatically_joint_to_this_role') ?>
			<input name="join_default" value="on" type="hidden">
		</p>
	<?php endif; ?>
</div>

<div class="<?php echo IS_AJAX ? 'modal-footer' : 'container-fluid pull-right' ?>">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(isset($role) ? lang('rl_update') : lang('rl_create'))?></button>
	<?php if (isset($role) && $role->join_default == 0): ?>
	<button class="an-btn an-btn-danger mb-btn-delete-role" data-role-id="<?php e($role->role_id)?>"><?php e(lang('rl_delete'))?></button>
	<?php endif; ?>
	<a href="#" class="an-btn an-btn-danger-transparent" <?php echo IS_AJAX ? 'data-dismiss="modal"' : '' ?>><?php e(lang('rl_cancel'))?></a>
</div>
<?php echo form_close(); ?>