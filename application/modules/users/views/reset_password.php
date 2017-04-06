
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
                            <h6><?php echo lang('us_reset_password'); ?></h6>

                            <div class="component-header-right">
                            </div>
						</div>
						<div class="an-component-body">
						<p class="an-small-doc-block"><?php echo lang('us_reset_password_note'); ?></p>

						<?php echo form_open($this->uri->uri_string(), array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
							<input type="hidden" name="user_id" value="<?php echo $user->user_id; ?>">

							<label><?php echo lang('bf_password') ?></label>
							<div class="an-input-group">
								<div class="an-input-group-addon"><i class="ion-key"></i></div>
								<input type="password" name="password" class="an-form-control <?php echo iif( form_error('password') , 'danger') ;?>" value="<?php echo set_value('password'); ?>">
							</div>

							<label><?php echo lang('bf_password_confirm') ?></label>
							<div class="an-input-group">
								<div class="an-input-group-addon"><i class="ion-key"></i></div>
								<input type="password" name="pass_confirm" class="an-form-control <?php echo iif( form_error('pass_confirm') , 'danger') ;?>" value="<?php echo set_value('pass_confirm'); ?>">
							</div>

							<button type="submit" name="set_password" class="an-btn an-btn-default fluid"><?php e(lang('us_set_password')); ?></button>
						<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
		</div> <!-- end an-flex-center-center -->
	</div>