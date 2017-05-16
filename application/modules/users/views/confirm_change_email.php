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
							<h6><?php echo lang('us_confirm_change_email'); ?></h6>

							<div class="component-header-right">
							</div>
						</div>
						<div class="an-component-body">
						<?php if (! empty($message)) : ?>
							<p class="an-small-doc-block warning"><?php echo lang('us_change_email_fail') ?></p>
							<button id="resend_confirm_mail" class="an-btn an-btn-default fluid"><?php echo lang('us_resend_confirm_mail') ?></button>
						<?php endif ?>
						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT -->
					</div> <!-- end an-login-container -->
				</div>
				</div> <!-- end row -->
			</div>
