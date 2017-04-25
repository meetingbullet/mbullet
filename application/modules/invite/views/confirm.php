	<div class="an-content-body">
		<?php echo form_open() ?>

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('iv_invitation')) ?></h6>
			</div>
			<div class="an-component-body">
				<div class="an-helper-block text-center">
					<div class='avatar' style='background-image:url("<?php echo avatar_url($invitation->inviter_avatar, $invitation->inviter_email) ?>")'></div>
					<i class='ion-ios-plus-empty big-plus'></i>
					<div class='avatar' style='background-image:url("<?php echo avatar_url($invitation->my_avatar, $invitation->my_email) ?>")'></div>
					<h4><?php echo sprintf(lang('iv_inviting_introduction'), $invitation->inviter_name, $invitation->organization_name)?></h4>
					<button type="submit" name="accept" class="an-btn an-btn-success"><?php e(lang('iv_accept'))?></button>
					<button type="submit" name="decline" class="an-btn an-btn-danger-transparent"><?php e(lang('iv_decline'))?></button>
				</div> <!-- end .AN-HELPER-BLOCK -->
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>


		<?php echo form_close(); ?>
	</div>