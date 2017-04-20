	<div class="<?php echo $this->input->is_ajax_request() ? '' : 'an-content-body'?>">

		<?php if ($this->input->is_ajax_request()): ?>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
			<h4 class="modal-title" id="myModaloneLabel"><?php e(lang('iv_invite_user'))?></h4>
		</div> <!-- end MODAL-HEADER -->
		<?php else: ?>
		<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
			<div class="an-page-title">
			<h2><?php e(lang('iv_invite_user'))?></h2>
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->
		<?php endif; ?>

		<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '']) ?>

		<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
				<?php echo mb_form_input('text', 'email', lang('iv_email'), true) ?>

				<div class="row">
					<div class="col-md-3 col-sm-12">
						<label for="email" class="pull-right"><?php e(lang('iv_role'))?></label>
					</div>
					<div class="col-md-9 col-sm-12">
						<select name="invite_role" class="an-form-control">
							<?php foreach($roles as $role): ?>
							<option value='<?php e($role->role_id)?>' 
								<?php e($this->input->post('invite_role') == $role->role_id ? 'selected' : ($role->join_default ? 'selected' : '')) ?>
							>
								<?php e($role->name .' - '. $role->description)?>
							</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
		</div>

		<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="add" class="an-btn an-btn-success"><?php e(lang('iv_add'))?></button>
			<a href="#" class="an-btn an-btn-success-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('iv_back'))?></a>
		</div>

		<?php echo form_close(); ?>
	</div>