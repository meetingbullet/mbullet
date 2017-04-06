<?php

$errorClass   = empty($errorClass) ? ' error' : $errorClass;
$controlClass = empty($controlClass) ? 'span6' : $controlClass;
$fieldData = array(
    'errorClass'    => $errorClass,
    'controlClass'  => $controlClass,
);

?>
<style scoped='scoped'>
#register p.already-registered {
    text-align: center;
}
</style>
<section id="register">
    <h1 class="page-header"><?php echo lang('us_sign_up'); ?></h1>
    <?php if (validation_errors()) : ?>
    <div class="alert alert-error fade in">
        <?php echo validation_errors(); ?>
    </div>
    <?php endif; ?>
    <div class="row-fluid">
        <div class="span12">
            <?php echo form_open(site_url(REGISTER_URL), array('class' => "form-horizontal", 'autocomplete' => 'off')); ?>
                <fieldset>
                    <div class="control-group">
                        <div class="controls">
							<label><?php echo lang('us_reg_email') ?></label>
                            <input type="text" name="email" value="<?php set_value('email') ?>" />
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