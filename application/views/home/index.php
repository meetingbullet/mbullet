<div class="an-body-topbar wow fadeIn">
	<div class="an-page-title">
		<h2>Welcome to Meeting Bullet</h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<div class="an-doc-block default with-shadow">
	<?php if (isset($current_user->email)) : ?>
		<a href="<?php echo site_url('dashboard'); ?>" class="btn btn-large btn-success">Go to the Admin area</a>
	<?php else :?>
		<a href="<?php echo site_url(LOGIN_URL); ?>" class="btn btn-large btn-primary"><?php echo lang('bf_action_login'); ?></a>
	<?php endif;?>
</div>