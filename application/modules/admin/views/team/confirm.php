	<div class="an-content-body">
		<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '']) ?>

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('iv_invitation')) ?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block">
				<h3><?php echo sprintf(lang('iv_inviting_introduction'), $invitation->inviter_name, $invitation->organization_name)?></h3>
				<p><?php echo sprintf(lang('iv_inviting_description'), $invitation->inviter_name)?></p>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>

		<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
			<button type="submit" name="accept" class="an-btn an-btn-success"><?php e(lang('iv_accept'))?></button>
			<button type="submit" name="decline" class="an-btn an-btn-danger-transparent"><?php e(lang('iv_decline'))?></button>
		</div>

		<?php echo form_close(); ?>
	</div>