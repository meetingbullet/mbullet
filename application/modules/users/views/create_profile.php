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
                            <h6><?php echo lang('us_create_profile'); ?></h6>

                            <div class="component-header-right">
                                <?php echo lang('us_already_registered'); ?>&nbsp
                                <p class="sign-up-link"><?php echo anchor(LOGIN_URL, lang('bf_action_login')); ?></p>
                            </div>
						</div>
						<div class="an-component-body">
							<?php echo form_open_multipart($this->uri->uri_string(), array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
								<div class="an-input-group">
                                    <label class='an-form-avatar-label'><?php echo lang('us_reg_avatar') ?></label>
                                    <div class="an-avatar">
                                        <img class='an-form-avatar-preview' id="user-avatar-preview" src="<?php echo img_path() . 'default_avatar.png' ?>"/>
                                        <div class='an-form-avatar-dim'><span><i class='ion-ios-upload-outline'></i></span></div>
                                        <input type="file" id="user-avatar" name="avatar" class="an-form-avatar"/>
                                    </div>
								</div>

								<label><?php echo lang('us_reg_email') ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-ios-email-outline"></i></div>
									<input type="text" name="email" class="an-form-control <?php echo iif( form_error('email') , 'danger') ;?>" value="<?php echo set_value('email', isset($_GET['email']) ? $_GET['email'] : '') ?>" readOnly/>
								</div>

								<label><?php echo lang('us_reg_name') ?></label>
								<div class="an-input-group">
									<div class="an-input-group-addon"><i class="ion-ios-person"></i></div>
									<input type="text" name="first_name" class="an-form-firstname an-form-control <?php echo iif( form_error('first_name') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_first_name') ?>" value="<?php echo set_value('first_name', isset($_POST['first_name']) ? $_POST['first_name'] : '') ?>"/>
									<input type="text" name="last_name" class="an-form-lastname an-form-control <?php echo iif( form_error('last_name') , 'danger') ;?>" placeholder="<?php echo lang('us_reg_last_name') ?>" value="<?php echo set_value('last_name', isset($_POST['last_name']) ? $_POST['last_name'] : '') ?>"/>
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
									<input type="text" name="skype" class="an-form-control <?php echo iif( form_error('skype') , 'danger') ;?>" value="<?php echo set_value('skype', isset($_POST['skype']) ? $_POST['skype'] : '') ?>"/>
								</div>

								<button type="submit" name="send" class="an-btn an-btn-default fluid"><?php e(lang('us_register')); ?></button>
							<?php echo form_close(); ?>

						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
		</div> <!-- end an-flex-center-center -->
	</div>