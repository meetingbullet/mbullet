<section>
    <h1 class="page-header"><?php echo lang('us_create_profile'); ?></h1>
    <?php if (validation_errors()) : ?>
    <div class="alert alert-error fade in">
        <?php echo validation_errors(); ?>
    </div>
    <?php endif; ?>
    <div class="row-fluid">
        <div class="span12">
            <?php echo form_open_multipart('', array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
                <fieldset>
					<div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_avatar') ?></label>
                            <input type="file" name="avatar"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_email') ?></label>
                            <input type="text" name="email" value="<?php echo set_value('email', isset($_GET['email']) ? $_GET['email'] : '') ?>" readOnly/>
                        </div>
                    </div>
					<div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_firstname') ?></label>
                            <input type="text" name="first_name" value="<?php echo set_value('first_name', isset($_POST['first_name']) ? $_POST['first_name'] : '') ?>"/>
                        </div>
                    </div>
					<div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_lastname') ?></label>
                            <input type="text" name="last_name" value="<?php echo set_value('last_name', isset($_POST['last_name']) ? $_POST['last_name'] : '') ?>"/>
                        </div>
                    </div>
					<div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_skype') ?></label>
                            <input type="text" name="skype" value="<?php echo set_value('skype', isset($_POST['skype']) ? $_POST['skype'] : '') ?>"/>
                        </div>
                    </div>
					<div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_password') ?></label>
                            <input type="password" name="password" value=""/>
                        </div>
                    </div>
					<div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_conf_password') ?></label>
                            <input type="password" name="conf_password" value=""/>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="control-group">
                        <div class="controls">
                            <input class="btn btn-primary" type="submit" name="register" id="submit" value="<?php echo lang('us_register'); ?>" />
                        </div>
                    </div>
                </fieldset>
            <?php echo form_close(); ?>
            <p class='already-registered'>
                <?php echo lang('us_already_registered'); ?>
                <?php echo anchor(LOGIN_URL, lang('bf_action_login')); ?>
            </p>
        </div>
    </div>
</section>