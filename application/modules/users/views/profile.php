<?php 
$defaultTimezone = isset($user->timezone) ? $user->timezone : strtoupper(settings_item('site.default_user_timezone'));
?>
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
							<h6><?php echo lang('us_edit_profile'); ?></h6>

							<div class="component-header-right">
							</div>
						</div>
						<div class="an-component-body">
							<?php echo form_open_multipart($this->uri->uri_string(), array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
								<div class="an-input-group">
									<label class='an-form-avatar-label'><?php echo lang('us_reg_avatar') ?></label>
									<div class="an-avatar">
										<img class='an-form-avatar-preview' id="user-avatar-preview" src="<?php echo $user->avatar ? img_path() . '/users/' . $user->avatar : img_path() . 'default_avatar.png' ?>"/>
										<div class='an-form-avatar-dim'><span><i class='ion-ios-upload-outline'></i></span></div>
										<input type="file" id="user-avatar" name="avatar" class="an-form-avatar"/>
									</div>
								</div>

								<label><?php echo lang('us_reg_email') ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-ios-email-outline"></i></div>
									<input type="text" name="email" class="an-form-control <?php echo iif( form_error('email') , 'danger') ;?>" value="<?php echo $user->email ?>" readOnly/>
								</div>

								<label><?php echo lang('us_reg_name') ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-ios-person"></i></div>
									<input type="text" name="first_name" class="an-form-firstname an-form-control <?php echo iif( form_error('first_name') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_first_name') ?>" value="<?php echo set_value('first_name', $user->first_name) ?>"/>
									<input type="text" name="last_name" class="an-form-lastname an-form-control <?php echo iif( form_error('last_name') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_last_name') ?>" value="<?php echo set_value('last_name', $user->last_name) ?>"/>
								</div>

								<label><?php echo lang('us_reg_password') ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-key"></i></div>
									<input type="password" name="password" class="an-form-control <?php echo iif( form_error('password') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_password') ?>"/>
								</div>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-key"></i></div>
									<input type="password" name="conf_password" class="an-form-control <?php echo iif( form_error('password') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_conf_password') ?>"/>
								</div>

								<label><?php echo lang('us_reg_skype') ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-social-skype"></i></div>
									<input type="text" name="skype" class="an-form-control <?php echo iif( form_error('skype') , 'danger') ;?>" value="<?php echo set_value('skype', $user->skype) ?>"/>
								</div>

								<label><?php echo lang('bf_timezone'); ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-social-skype"></i></div>
									<?php
									echo timezone_menu(
										set_value('timezone', isset($user) ? $user->timezone : $defaultTimezone),
										'an-form-control',
										'timezone',
										array('id' => 'timezone')
									);
									?>
								</div>

								<button type="submit" name="save" class="an-btn an-btn-default fluid"><?php e(lang('us_update_profile')); ?></button>
							<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
