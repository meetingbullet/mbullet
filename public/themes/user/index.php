<?php 
echo theme_view('header');
echo theme_view('_sitenav');

$message = Template::message();

if ($message) : ?>
	<div class="an-notification-content top-full-width">
			<?php echo $message; ?>
	</div>
<?php 
endif;

echo isset($content) ? $content : Template::content();

echo theme_view('footer');
?>