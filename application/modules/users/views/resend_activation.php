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
						<h6><?php echo lang('us_activate_resend'); ?></h6>
						<div class="component-header-right">
						</div>
						</div>
						<div class="an-component-body">
						<?php echo form_open($this->uri->uri_string(), array('autocomplete' => 'off')); ?>
							<p class="an-small-doc-block"><?php echo lang('us_activate_resend_note'); ?></p>

							<label><?php echo lang('bf_email'); ?></label>
							<div class="an-input-group">
								<div class="an-input-group-addon"><i class="ion-ios-email-outline"></i></div>
								<input type="text" name="email" class="an-form-control <?php echo iif( form_error('email') , 'danger') ;?>">
							</div>

							<button type="submit" name="send" class="an-btn an-btn-default fluid"><?php e(lang('us_activate_code_send')); ?></button>
						<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
		</div> <!-- end an-flex-center-center -->
	</div>


<div class="page-header">
	<h1><?php echo lang('us_activate_resend'); ?></h1>
</div>

<?php if (validation_errors()) { ?>
	<div class="alert alert-error fade in">
		<?php echo validation_errors(); ?>
	</div>
<?php } else { ?>

	<div class="well shallow-well">
		<?php echo lang('us_activate_resend_note'); ?>
	</div>
<?php } ?>
<div class="row-fluid">
	<div class="span8 offset2">

<?php echo form_open($this->uri->uri_string(), array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>

	<div class="control-group <?php echo iif( form_error('email') , 'error') ;?>">
		<label class="control-label required" for="email"><?php echo lang('bf_email'); ?></label>
		<div class="controls">
			<input class="span6" type="text" name="email" id="email" value="<?php echo set_value('email') ?>" />
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<input class="btn btn-primary" type="submit" name="send" value="<?php echo lang('us_activate_code_send') ?>"  />
		</div>
	</div>

<?php echo form_close(); ?>

	</div>
</div>
