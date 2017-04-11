<?php
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
						<h6><?php echo lang('us_activate'); ?></h6>
						<div class="component-header-right">
						</div>
						</div>
						<div class="an-component-body">
						<?php echo form_open($this->uri->uri_string(), array('autocomplete' => 'off')); ?>
							<p class="an-small-doc-block"><?php echo lang('us_user_activate_note'); ?></p>

							<label><?php echo lang('us_activate_code'); ?></label>
							<div class="an-input-group">
								<div class="an-input-group-addon"><i class="ion-key"></i></div>
								<input type="text" name="code" class="an-form-control <?php echo iif( form_error('code') , 'danger') ;?>">
							</div>

							<button type="submit" name="activate" class="an-btn an-btn-default fluid"><?php e(lang('us_confirm_activate_code')); ?></button>
						<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
		</div> <!-- end an-flex-center-center -->
	</div>
