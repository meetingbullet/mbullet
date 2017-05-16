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
							<div class="an-bootstrap-custom-tab">
								<div class="an-tab-control">
									<!-- Nav tabs -->
									<ul class="nav nav-tabs text-center" role="tablist">
										<li role="presentation" class="active">
											<a href="#diska" aria-controls="diska" role="tab" data-toggle="tab" aria-expanded="true"><?php echo lang('us_profile') ?></a>
										</li>
										<li role="presentation" class="">
											<a href="#diskb" aria-controls="diskb" role="tab" data-toggle="tab" aria-expanded="false"><?php echo lang('us_password') ?></a>
										</li>
									</ul>
								</div>

								<!-- Tab panes -->
								<div class="tab-content">
									<div role="tabpanel" class="tab-pane fade in active" id="diska">
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
											<input type="text" name="new_email" class="an-form-control <?php echo iif( form_error('email') , 'danger') ;?>" value="<?php echo set_value('email', $user->chosen_email) ?>"/>
											<?php if ($user->chosen_email != $user->email) : ?>
												<p class="an-small-doc-block"><?php echo sprintf(lang('us_waiting_for_confirmation_or_resend_mail'), '#'); ?></p>
											<?php endif ?>
										</div>

										<label><?php echo lang('us_reg_name') ?></label>
										<div class="an-input-group">
											<div class="an-input-group-addon"><i class="ion-ios-person"></i></div>
											<input type="text" name="first_name" class="an-form-firstname an-form-control <?php echo iif( form_error('first_name') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_first_name') ?>" value="<?php echo set_value('first_name', $user->first_name) ?>"/>
											<input type="text" name="last_name" class="an-form-lastname an-form-control <?php echo iif( form_error('last_name') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_last_name') ?>" value="<?php echo set_value('last_name', $user->last_name) ?>"/>
										</div>

										<label><?php echo lang('us_reg_skype') ?></label>
										<div class="an-input-group">
											<div class="an-input-group-addon"><i class="ion-social-skype"></i></div>
											<input type="text" name="skype" class="an-form-control <?php echo iif( form_error('skype') , 'danger') ;?>" value="<?php echo set_value('skype', $user->skype) ?>"/>
										</div>

										<label><?php echo lang('bf_timezone'); ?></label>
										<div class="an-input-group">
											<div class="an-input-group-addon"><i class="ion-earth"></i></div>
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
									</div> <!-- end .TAB-PANE -->

									<div role="tabpanel" class="tab-pane fade" id="diskb">
									<?php echo form_open_multipart($this->uri->uri_string(), array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
										<label><?php echo lang('us_reg_password') ?></label>
										<div class="an-input-group">
											<div class="an-input-group-addon"><i class="ion-key"></i></div>
											<input type="password" name="current_password" class="an-form-control <?php echo iif( form_error('current_password') || ($old_password_matched === false) , 'danger') ;?>" placeholder="<?php echo lang('us_current_password') ?>"/>
										</div>
										<div class="an-input-group">
											<div class="an-input-group-addon"><i class="ion-key"></i></div>
											<input type="password" name="new_password" class="an-form-control <?php echo iif( form_error('new_password') , 'danger') ;?>" placeholder="<?php echo lang('us_new_password') ?>"/>
										</div>
										<div class="an-input-group">
											<div class="an-input-group-addon"><i class="ion-android-done-all"></i></div>
											<input type="password" name="conf_password" class="an-form-control <?php echo iif( form_error('conf_password') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_conf_password') ?>"/>
										</div>

										<div class="text-center">
											<a href="#" id="forgot_password"><i class="ion-help-circled"></i><?php echo ' ' . lang('us_forgot_password') ?></a>
										</div>

										<button type="submit" name="save_password" class="an-btn an-btn-default fluid"><?php e(lang('us_update_password')); ?></button>
									<?php echo form_close(); ?>
									</div> <!-- end .TAB-PANE -->
								</div> <!-- end .TAB-CONTENT -->
							</div>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
