			<div class='mb-rainbow'>
				<div class='dark-blue'></div>
				<div class='nearly-green'></div>
				<div class='leaf-green'></div>
				<div class='almost-yellow'></div>
				<div class='mostly-pink'></div>
			</div>

			<div class="container">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<div class="an-login-container">
							<div class="an-single-component with-shadow">
								<div class="an-component-header">
									<a class="an-logo-link" href="<?php e(base_url())?>"><img src="<?php echo Template::theme_url('images/logo.svg') ?>" class='logo-login'/></a>
									<div class="component-header-right">
										<?php if ($this->settings_lib->item('auth.allow_register')) : ?>
										<p class="sign-up-link">
											<?php echo lang('us_dont_have_account') . anchor(REGISTER_URL, lang('us_sign_up'), ['class' => 'mb-link']); ?>
										</p>
										<?php endif; ?>
									</div>
								</div>
								<?php echo form_open(LOGIN_URL, array('autocomplete' => 'off', 'class' => 'mb-form')); ?>
									<div class="an-component-body addon">
										<a href="<?php echo $auth_url ?>" class="an-btn an-btn-google block-icon fluid">
											<i class="fa fa-google-plus" aria-hidden="true"></i>
											<?php e(' ' . lang('us_let_me_in') . ' ' . lang('us_with_google')); ?>
										</a>
									</div>
									<div class="an-component-body">
											<label class='mb-uppercase'><?php echo $this->settings_lib->item('auth.login_type') == 'both' ? lang('us_email_address_or_username') : ucwords($this->settings_lib->item('auth.login_type')) ?> <span class='required'>*</span></label>
												<input type="text" name="login" class="an-form-control <?php echo iif( form_error('login') , ' danger') ;?>" value="<?php echo set_value('login'); ?>" tabindex="1" placeholder="<?php echo lang('us_email_example')?> ">

											<label class='mb-uppercase'><?php echo lang('us_your_password'); ?> <span class='required'>*</span></label>
											<div class='pull-right'>
												<?php echo anchor('/forgot_password', lang('us_forgot_your_password'), ['class' => 'mb-link']); ?>
											</div>
											<input type="password" name="password" class="an-form-control <?php echo iif( form_error('password') , ' danger') ;?>" tabindex="2" placeholder="*******">

											<button name="log-me-in" class="an-btn an-btn-default fluid"><?php e(lang('us_let_me_in')); ?></button>

											<div class="remembered-section">
												<span class="an-custom-checkbox">
													<input type="checkbox" name="remember_me" id="remember_me" value="1" tabindex="3" />
													<label for="remember_me"><?php echo lang('us_remember_note'); ?></label>
												</span>
											</div>

									</div> <!-- end .AN-COMPONENT-BODY -->
								<?php echo form_close(); ?>
							</div> <!-- end .AN-SINGLE-COMPONENT -->
						</div> <!-- end an-login-container -->
					</div>
				</div> <!-- end row -->
			</div>
<script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>