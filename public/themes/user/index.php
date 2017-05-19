<?php
echo theme_view('header');
echo theme_view('header_bar');
echo Template::message();
?>
		<div class="an-page-content">
<?php
// echo theme_view('_sitenav');
?>

			<div class="an-content-body">
<?php
echo theme_view('_breadcrumb');
echo isset($content) ? $content : Template::content();
?>
			</div> <!-- end .AN-PAGE-CONTENT -->
		</div> <!-- end .AN-PAGE-CONTENT -->
<?php
echo theme_view('footer');
?>