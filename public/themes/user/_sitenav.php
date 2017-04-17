			<div class="an-sidebar-nav js-sidebar-toggle-with-click">
				<div class="an-sidebar-search">
					<form class="an-form" action="#">
					<a class="collapse-sidebar-search-btn js-search-toggle" href="#"><i class="icon-search"></i></a>
					<div class="an-search-field topbar js-search-show-clicked">
						<input class="an-form-control no-redius border-bottom light-text" type="text" placeholder="Search...">
						<button class="an-btn an-btn-icon sidebar-search" type="submit"><i class="icon-search"></i></button>
					</div>
					</form>
				</div> <!-- end .AN-SIDEBAR-SEARCH -->

				<!--<div class="an-sidebar-widgets">
					<div class="widget-signle">
						<h5 class="counter-result">789</h5>
						<p>Sales</p>
					</div>
					<div class="widget-signle">
						<h5 class="counter-result">1,234</h5>
						<p>Order</p>
					</div>
					<div class="widget-signle">
						<h5 class="counter-result">$900</h5>
						<p>Send</p>
					</div>
				</div> end .AN-SIDEBAR-WIDGETS -->

				<div class="an-sidebar-nav">
					<?php echo Contexts::render_menu('both', 'normal'); ?>
				</div> <!-- end .AN-SIDEBAR-NAV -->
			</div> <!-- end .AN-SIDEBAR-NAV -->
