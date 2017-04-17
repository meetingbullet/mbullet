	<div class="an-page-content">
        <div class="an-flex-center-center">
			<div class="container">
				<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<div class="an-login-container">
					<div class="back-to-home">
						<h3 class="an-logo-heading text-center">
						<a class="an-logo-link" href="<?php e(base_url())?>"><?php e($this->settings_lib->item('site.title')) ?>
							<span><?php e($this->settings_lib->item('site.description')) ?></span>
						</a>
						</h3>
					</div>
					<div class="an-single-component with-shadow">
						<div class="an-component-header">
                            <h6><?php echo lang('us_reset_password'); ?></h6>

                            <div class="component-header-right">
                                <?php echo lang('us_already_registered'); ?>&nbsp
                                <p class="sign-up-link"><?php echo anchor(LOGIN_URL, lang('bf_action_login')); ?></p>
                            </div>
						</div>
						<div class="an-component-body">
							<p class="an-small-doc-block"><?php echo lang('us_reset_note'); ?></p>
							
							<?php echo form_open($this->uri->uri_string(), array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
								<label><?php echo lang('us_reg_email') ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-ios-email-outline"></i></div>
									<input type="text" name="email" class="an-form-control <?php echo iif( form_error('email') , 'danger') ;?>" value="<?php echo set_value('email'); ?>">
								</div>

								<button type="submit" name="send" class="an-btn an-btn-default fluid"><?php e(lang('us_send_password')); ?></button>
							<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
		</div> <!-- end an-flex-center-center -->
	</div>