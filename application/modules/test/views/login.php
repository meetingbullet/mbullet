<div id="login">
	<?php echo Template::message(); ?>
	<?php echo form_open('' , 'method="post"') ?>
		<div class="input-prepend">
			<span class="add-on">Email</span>
			<input class="span2" name="email" id="prependedInput" type="text">
		</div>

		<div class="input-prepend">
			<span class="add-on">Password</span>
			<input class="span2" name="password" id="prependedInput" type="password">
		</div>

		<div>
			<button type="submit" class="btn btn-primary">Login</button>
		</div>
	<?php echo form_close() ?>
</div>