<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo $heading; ?></title>

		<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet">
		<link href="/themes/default/css/animate.min.css" rel="stylesheet">
		<link href="/themes/default/css/vendor-styles.css" rel="stylesheet">
		<link rel="stylesheet" href="/themes/default/css/styles.css">

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
				<img src="/themes/default/images/loader.png" alt="">
			</div>
			<div class="an-page-content">
				<div class="an-flex-center-center">
					<h3 class="an-logo-heading text-center wow fadeInDown">
						<a class="an-logo-link" href="/"><img src="/assets/images/logo-black.svg" height="32"></a>
					</h3>
					<div class="an-4040-page">
						<h1>404</h1>
						<?php echo $message; ?>
					</div>
					<div class="back-to-home wow fadeInUp">
						<a href="/" class="an-btn an-btn-default">Back to home</a>
					</div>

					<!-- end an-login-container -->
					<!-- end row -->

				</div>
			</div> <!-- end .AN-PAGE-CONTENT -->

		</div> <!-- end .MAIN-WRAPPER -->
		<script src="/themes/default/js-plugins/jquery-3.1.1.min.js" type="text/javascript"></script>
		<script src="/themes/default/js-plugins/bootstrap.min.js" type="text/javascript"></script>
		<script src="/themes/default/js-plugins/perfect-scrollbar.jquery.min.js" type="text/javascript"></script>
		<script src="/themes/default/js-plugins/wow.min.js" type="text/javascript"></script>

		<!-- MAIN SCRIPTS START FROM HERE above scripts from plugin -->
		<script src="/themes/default/js/scripts.js" type="text/javascript"></script>
	</body>
</html>
