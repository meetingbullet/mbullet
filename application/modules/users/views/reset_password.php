			<div class="container">
				<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<div class="an-login-container">
					<div class="back-to-home">
						<a class="an-logo-link" href="<?php e(base_url())?>"><img src="<?php echo img_path() . 'logo-black.svg' ?>" class='logo-login'/></a>
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
