	</div> <!-- end .MAIN-WRAPPER -->

	<div id="debug"><!-- Stores the Profiler Results --></div>
	<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script>window.jQuery || document.write('<script src="<?php echo js_path(); ?>jquery-3.2.1.min.js"><\/script>');</script>
	<script src="<?php echo js_path(); ?>jquery-ui.min.js"></script>

	<!-- Theme Script -->
	<script src="<?php echo Template::theme_url("js-plugins/bootstrap.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/moment.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/daterangepicker.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/wow.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/perfect-scrollbar.jquery.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/selectize.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/owl.carousel.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/Chart.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/circle-progress.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/bootstrap-datetimepicker.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/readmore.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/jsrender.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/fullcalendar.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/gcal.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js/customize-chart.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/bootstrap-notify.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/sweetalert.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/bootstrap-switch.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js-plugins/lc_switch.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js/bootstrap-editable.min.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js/scripts.js"); ?>" type="text/javascript"></script>
	<script src="<?php echo Template::theme_url("js/main.js"); ?>" type="text/javascript"></script>

	<?php echo Assets::js(); ?>
</body>
</html>