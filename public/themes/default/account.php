<?php
echo theme_view('header');
echo Template::message();
?>
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