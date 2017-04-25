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
					<h6><?php echo lang('org_create') . ' ' . lang('org_organization'); ?></h6>
					<div class="component-header-right">
					</div>
					</div>
					<div class="an-component-body">
					<?php echo form_open('', 'autocomplete="off"'); ?>
						<p class="an-small-doc-block"><?php echo lang('org_create_notice') ?></p>
						<label><?php echo lang('org_name') ?></label>
						<div class="an-input-group">
							<div class="an-input-group-addon"><i class="ion-briefcase"></i></div>
							<input type="text" name="name" id="input_trigger" class="an-form-control <?php echo iif( form_error('name') , 'danger') ;?>" value="<?php echo set_value('name'); ?>" tabindex="1">
						</div>

						<label><?php echo lang('org_url'); ?></label>
						<div class="an input-group">
							<div class="input-group-addon"><?php echo is_https() ? 'https://' : 'http://' ?></div>
							<input type="text" name="url" id="input_triggered" class="an-form-control form-control <?php echo iif( form_error('url') , 'danger') ;?>" tabindex="2" value="<?php echo set_value('url') ?>">
							<div class="input-group-addon">.<?php echo $this->domain->get_main_domain() ?></div>
						</div>

						<button name="create" class="an-btn an-btn-default fluid"><?php e(lang('org_create')); ?></button>
					<?php echo form_close(); ?>

					</div> <!-- end .AN-COMPONENT-BODY -->
				</div> <!-- end .AN-SINGLE-COMPONENT -->
				</div> <!-- end an-login-container -->
			</div>
			</div> <!-- end row -->
		</div>
