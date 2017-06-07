<?php

$inline  = '$(".dropdown-toggle").dropdown();';
$inline .= '$(".tooltips").tooltip();';
Assets::add_js($inline, 'inline');
Assets::add_css(['bootstrap-editable.css', 'fullcalendar.min.css', 'animate.min.css', 'vendor-styles.css', 'styles.css']);

?>
<!doctype html>
<head>
	<meta charset="utf-8">
	<title><?php
		echo isset($page_title) ? "{$page_title} : " : '';
		e(class_exists('Settings_lib') ? settings_item('site.title') : 'Bonfire');
	?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="description" content="<?php e(isset($meta_description) ? $meta_description : ''); ?>">
	<meta name="author" content="<?php e(isset($meta_author) ? $meta_author : ''); ?>">
	<?php
	/* Modernizr is loaded before CSS so CSS can utilize its features */
	echo Assets::js('modernizr-2.5.3.js');
	echo Assets::css();
	?>

	<!-- Theme CSS -->
	<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">

	<link rel="shortcut icon" href="<?php echo base_url(); ?>favicon.ico">


	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<div class="main-wrapper">
		<div class="an-loader-container">
			<img src="<?php echo Template::theme_url("images/loader.png"); ?>" alt="">
		</div>