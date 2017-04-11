<?php
	$site_open = $this->settings_lib->item('auth.allow_register');
	$message = Template::message();
?>

	<?php if (validation_errors() || $message) : ?>
	<div class="an-notification-content top-full-width">
		<?php if(validation_errors()): ?>
		<div class="alert alert-danger  js-nofitication-body" role="alert" style="">
			<button type="button" class="close"><span aria-hidden="true">×</span></button>
			<?php echo validation_errors() ?>
		</div>
		<?php else: ?>
			<?php echo $message; ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div class="an-page-content">
        <div class="an-flex-center-center">
			<div class="container">
				<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<div class="an-login-container">
					<div class="back-to-home">
						<h3 class="an-logo-heading text-center wow fadeInDown">
						<a class="an-logo-link" href="<?php e(base_url())?>"><?php e($this->settings_lib->item('site.title')) ?>
							<span><?php e($this->settings_lib->item('site.description')) ?></span>
						</a>
						</h3>
					</div>
					<div class="an-single-component with-shadow">
						<div class="an-component-header">
						<h6><?php echo lang('us_login'); ?></h6>
						<div class="component-header-right">
							<?php if ( $site_open ) : ?>
							<p class="sign-up-link">
								<?php echo anchor(REGISTER_URL, lang('us_sign_up')); ?>
							</p>
							<?php endif; ?>
						</div>
						</div>
						<div class="an-component-body">
						<?php echo form_open(LOGIN_URL, array('autocomplete' => 'off')); ?>
							<label><?php echo $this->settings_lib->item('auth.login_type') == 'both' ? lang('bf_username') .'/'. lang('bf_email') : ucwords($this->settings_lib->item('auth.login_type')) ?></label>
							<div class="an-input-group">
								<div class="an-input-group-addon"><i class="ion-ios-email-outline"></i></div>
								<input type="text" name="login" class="an-form-control <?php echo iif( form_error('login') , 'danger') ;?>" value="<?php echo set_value('login'); ?>" tabindex="1">
							</div>

							<label><?php echo lang('bf_password'); ?></label>
							<div class="an-input-group">
								<div class="an-input-group-addon"><i class="ion-key"></i></div>
								<input type="password" name="password" class="an-form-control <?php echo iif( form_error('password') , 'danger') ;?>" tabindex="2" >
							</div>

							<div class="remembered-section">
								<span class="an-custom-checkbox">
									<input type="checkbox" name="remember_me" id="remember_me" value="1" tabindex="3" />
									<label for="remember_me"><?php echo lang('us_remember_note'); ?></label>
								</span>
								<?php echo anchor('/forgot_password', lang('us_forgot_your_password')); ?>
							</div>

							<button name="log-me-in" class="an-btn an-btn-default fluid"><?php e(lang('us_let_me_in')); ?></button>
							<a href="<?php echo $auth_url ?>" style="margin-top: 5px;" class="an-btn an-btn-default fluid"><i class="fa fa-google-plus" aria-hidden="true"></i><?php e(' ' . lang('us_let_me_in') . ' ' . lang('us_with_google')); ?></a>
						<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
		</div> <!-- end an-flex-center-center -->
	</div>
	<script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>