	<?php if (validation_errors() || Template::message()) : ?>
	<div class="an-notification-content top-full-width">
		<?php if(validation_errors()): ?>
		<div class="alert alert-danger  js-nofitication-body" role="alert" style="">
			<button type="button" class="close"><span aria-hidden="true">×</span></button>
			<?php echo validation_errors() ?>
		</div>
		<?php else: ?>
			<?php echo iif(validation_errors(), validation_errors(), Template::message()); ?>
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
                            <h6><?php echo lang('us_sign_up'); ?></h6>
                            <div class="component-header-right">
                                <?php echo lang('us_already_registered'); ?>&nbsp
                                <p class='sign-up-link'><?php echo anchor(LOGIN_URL, lang('bf_action_login')); ?></p>
                            </div>
						</div>
						<div class="an-component-body">
						<?php echo form_open(REGISTER_URL, array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
							<label><?php echo lang('us_reg_email') ?></label>
							<div class="an-input-group">
								<div class="an-input-group-addon"><i class="ion-ios-email-outline"></i></div>
								<input type="text" name="email" class="an-form-control <?php echo iif( form_error('email') , 'danger') ;?>" value="<?php echo set_value('email'); ?>" tabindex="1">
							</div>

							<button type="submit" name="register" class="an-btn an-btn-default fluid"><?php echo lang('us_continue'); ?></button>
						<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
		</div> <!-- end an-flex-center-center -->
 	</div>