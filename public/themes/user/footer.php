            </div> <!-- end .AN-PAGE-CONTENT -->
        </div> <!-- end .AN-PAGE-CONTENT -->

        <footer class="an-footer">
            <p>COPYRIGHT 2017 © SIXTH GEAR STUDIOS. ALL RIGHTS RESERVED</p>
        </footer> <!-- end an-footer -->
    </div> <!-- end .MAIN-WRAPPER -->

	<div id="debug"><!-- Stores the Profiler Results --></div>
    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="<?php echo js_path(); ?>jquery-1.12.4.min.js"><\/script>');</script>
	<script src="<?php echo js_path() . 'jquery-ui.min.js' ?>"></script>
    <?php echo Assets::js(); ?>

    <!-- Theme Script -->
    <script src="<?php echo Template::theme_url("js-plugins/moment.min.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js-plugins/daterangepicker.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js-plugins/wow.min.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js-plugins/perfect-scrollbar.jquery.min.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js-plugins/selectize.min.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js-plugins/owl.carousel.min.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js-plugins/Chart.min.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js-plugins/circle-progress.min.js"); ?>" type="text/javascript"></script>

    <script src="<?php echo Template::theme_url("js/customize-chart.js"); ?>" type="text/javascript"></script>
    <script src="<?php echo Template::theme_url("js/scripts.js"); ?>" type="text/javascript"></script>
</body>
</html>