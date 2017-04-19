<?php
echo theme_view('header');
echo Template::message();
?>

		<div class='mb-rainbow'>
			<div class='dark-blue'></div>
			<div class='nearly-green'></div>
			<div class='leaf-green'></div>
			<div class='almost-yellow'></div>
			<div class='mostly-pink'></div>
		</div>
		
		<div class="an-page-content login-page">
			<div class="an-flex-center-center">
<?php
echo isset($content) ? $content : Template::content();
?>
			</div> <!-- end .AN-PAGE-CONTENT -->
		</div> <!-- end .AN-PAGE-CONTENT -->
<?php
echo theme_view('footer');
?>