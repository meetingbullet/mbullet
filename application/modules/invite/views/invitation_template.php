<strong><?php echo $inviter_name ?></strong> has invited you to join the <strong><?php echo $organization_name ?></strong> organization. 
Click the link below to view your invitation<br>
<?php echo $link ?>

<!--
<style type="text/css">
	html, body {
		margin: 0;
		padding: 0;
		color: #333;
		background: #FAFAFA;
	}

	a {
		text-decoration: none;
		color: #5A87CF;
	}

	.center {
		padding: 10px;
		background: #FAFAFA;
		font-size: 15px;
		font-family: Arial, sans-serif;
		line-height: 150%;
	}

	h2 {
		margin: 20px 50px;
		font-weight: normal;
		text-align: center;
		line-height: 150%;
	}

	hr {
		background: #E6E6E6;
		height: 1px;
		border: 0;
	}

	.logo {
		width: 180px;
		margin: 20px;
	}

	.avatar {
		display: block;
		width: 50px;
		height: 50px;
		border-radius: 50%;
		background: url('<?php echo strstr($avatar, 'http') ? $avatar : img_path() . 'users/'. $avatar ?>');
		background-size: cover;
	}

	.button {
		font-weight: bold;
		line-height: 100%;
		width: 200px;
		padding: 15px 20px;
		background: #81CCB7;
		color: white;
		display: inline-block;
		text-align: center;
    	vertical-align: middle;
		border-radius: 5px;
		margin: 15px;
	}

	.subtitle {
		color: #777;
		font-size: 13px;
	}

	.container {
		border: 1px solid #E6E6E6;
		border-radius: 5px;
		max-width: 650px;
		background: white;
		padding: 20px;
		text-align: left;
	}
</style>

<table class='center' width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td align="center">
			<a href="http://mb.vn">
				<img src="http://sixthgearstudios.mb.vn/assets/images/icon.svg" title="Go to Meeting Bullet" class='logo' />
			</a>


			<div class='container'>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="center">
							<div class='avatar'></div>
						</td>
					</tr>
				</table>
					
				<h2>
					<strong><?php e($inviter_name) ?></strong> has invited you to join the <strong><?php e($organization_name) ?></strong> organization
				</h2>
				<hr/>
				<p>
					You can <a href="<?php echo $link ?>">accept or decline</a> this invitation.
					You can also head over to <a href='#'><?php e($organization_name) ?></a> 
					to check out the organization or visit <a href='#'><?php e($inviter_name) ?></a> profile 
					to learn a bit about them.
				</p>

				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td align="center">
							<a href="<?php echo $link ?>" class='button'>View your invitation</a>
						</td>
					</tr>
				</table>

				<p>
					<strong>Note:</strong> This invitation was intended for 
					<a href='#'><?php e($email) ?></a>. 
						If you were not expecting this invitation, you can click decline. 
						If <?php e($inviter_name) ?> is sending you too many invitation, you can <a href='#'>block them</a> or <a href='#'>report abuse.</a>
					</a>
				</p>

				<hr>
				<p class='subtitle'>
					<strong>Button not working?</strong> Please copy and paste this link into your browser:<br>
					<?php echo $link ?>
				</p>
			</div>

			<p>
				<a href="http://mb.vn">
					Manage your <strong>Meeting Bullet</strong> email preferences
				</a>
			</p>
			
			<p class='subtitle' ><a href="http://mb.vn">Term</a> • <a href="http://mb.vn">Privacy</a> • <a href="http://mb.vn">Login Meeting Bullet</a></p>
		</td>
	</tr>
</table>
-->