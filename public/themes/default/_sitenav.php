        <header class="an-header" style="visibility: visible; animation-name: fadeInDown;">
            <div class="an-topbar-left-part">
            <h3 class="an-logo-heading">
                <a class="an-logo-link" href="#"><?php echo $this->settings_lib->item('site.title'); ?>
                <span><?php echo $this->settings_lib->item('site.description'); ?></span>
                </a>
            </h3>
            <button class="an-btn an-btn-icon toggle-button js-toggle-sidebar">
                <i class="icon-list"></i>
            </button>
            <form class="an-form" action="#">
                <div class="an-search-field topbar">
                <input class="an-form-control" type="text" placeholder="Search...">
                <button class="an-btn an-btn-icon" type="submit">
                    <i class="icon-search"></i>
                </button>
                </div>
            </form>
            </div> <!-- end .AN-TOPBAR-LEFT-PART -->

            <div class="an-topbar-right-part">
            <div class="an-notifications">
                <div class="btn-group an-notifications-dropown notifications">
                <button type="button" class="an-btn an-btn-icon dropdown-toggle js-has-new-notification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ion-ios-bell-outline"></i>
                </button>
                <div class="dropdown-menu">
                    <p class="an-info-count">Notifications <span>3</span></p>
                    <div class="an-info-content notifications-info notifications-content ps-container ps-theme-default" data-ps-id="0f0d7128-aed9-6089-d33a-0f028f90e788">
                    <div class="an-info-single unread">
                        <a href="#">
                        <span class="icon-container important">
                            <i class="icon-setting"></i>
                        </span>
                        <div class="info-content">
                            <h5 class="user-name">Settings updated</h5>
                            <p class="content"><i class="icon-clock"></i> 30 min ago</p>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single unread">
                        <a href="#">
                        <span class="icon-container success">
                            <i class="icon-cart"></i>
                        </span>
                        <div class="info-content">
                            <h5 class="user-name">5 Orders placed</h5>
                            <p class="content"><i class="icon-clock"></i> 1 hour ago</p>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single unread">
                        <a href="#">
                        <span class="icon-container nutral">
                            <i class="icon-chat-o"></i>
                        </span>
                        <div class="info-content">
                            <h5 class="user-name">3 New messages </h5>
                            <p class="content"><i class="icon-clock"></i> 1 hour ago</p>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single">
                        <a href="#">
                        <span class="icon-container warning">
                            <i class="icon-alerm"></i>
                        </span>
                        <div class="info-content">
                            <h5 class="user-name">This is warning notification</h5>
                            <p class="content"><i class="icon-clock"></i> 1 hour ago</p>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single">
                        <a href="#">
                        <span class="icon-container danger"><i class="icon-danger"></i></span>
                        <div class="info-content">
                            <h5 class="user-name">Server loaded by 98% please recover soon</h5>
                            <p class="content"><i class="icon-clock"></i> 1 hour ago</p>
                        </div>
                        </a>
                    </div>
                    <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 0px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div></div></div> <!-- end .AN-INFO-CONTENT -->
                    <div class="an-info-show-all-btn">
                    <a class="an-btn an-btn-transparent fluid rounded uppercase small-font" href="#">Show all</a>
                    </div>
                </div>
                </div>
            </div> <!-- end .AN-NOTIFICATION -->

            <div class="an-messages">
                <div class="btn-group an-notifications-dropown messages">
                <button type="button" class="an-btn an-btn-icon dropdown-toggle js-has-new-messages" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="ion-ios-email-outline"></i>
                </button>
                <div class="dropdown-menu">
                    <p class="an-info-count">Messages <span>3</span></p>
                    <div class="an-info-content notifications-info ps-container ps-theme-default" data-ps-id="f8e89032-777a-5cdb-3b04-d217ab26808d">
                    <div class="an-info-single unread">
                        <a href="#">
                        <span class="user-img" style="background-image: url('<?php echo Template::theme_url("images/users/user1.jpg"); ?>')"></span>
                        <div class="info-content">
                            <h5 class="user-name">Ana malik</h5>
                            <p class="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do.</p>
                            <span class="info-time"><i class="icon-clock"></i>15:28</span>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single unread">
                        <a href="#">
                        <span class="user-img" style="background-image: url('<?php echo Template::theme_url("images/users/user2.jpg"); ?>')"></span>
                        <div class="info-content">
                            <h5 class="user-name">Jackson Fred</h5>
                            <p class="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do.</p>
                            <span class="info-time"><i class="icon-clock"></i>4:54</span>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single">
                        <a href="#">
                        <span class="user-img" style="background-image: url('<?php echo Template::theme_url("images/users/user3.jpg"); ?>')"></span>
                        <div class="info-content">
                            <h5 class="user-name">Emma Watson</h5>
                            <p class="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do.</p>
                            <span class="info-time"><i class="icon-clock"></i>28 Sep</span>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single">
                        <a href="#">
                        <span class="user-img" style="background-image: url('<?php echo Template::theme_url("images/users/user4.jpg"); ?>')"></span>
                        <div class="info-content">
                            <h5 class="user-name">Elina</h5>
                            <p class="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do.</p>
                            <span class="info-time"><i class="icon-clock"></i>28 Sep</span>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single">
                        <a href="#">
                        <span class="user-img" style="background-image: url('<?php echo Template::theme_url("images/users/user5.jpg"); ?>')"></span>
                        <div class="info-content">
                            <h5 class="user-name">Jack Elison</h5>
                            <p class="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do.</p>
                            <span class="info-time"><i class="icon-clock"></i>20 Sep</span>
                        </div>
                        </a>
                    </div>

                    <div class="an-info-single">
                        <a href="#">
                        <span class="user-img" style="background-image: url('<?php echo Template::theme_url("images/users/user6.jpg"); ?>')"></span>
                        <div class="info-content">
                            <h5 class="user-name">Lara Smith</h5>
                            <p class="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do.</p>
                            <span class="info-time"><i class="icon-clock"></i>10 Sep</span>
                        </div>
                        </a>
                    </div>
                    <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 0px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div></div></div> <!-- end .AN-INFO-CONTENT -->

                    <div class="an-info-show-all-btn">
                    <a class="an-btn an-btn-transparent fluid rounded uppercase small-font" href="#">Show all</a>
                    </div>
                </div>
                </div>
            </div> <!-- end .AN-MESSAGE -->

            <div class="an-profile-settings">
                <div class="btn-group an-notifications-dropown  profile">
                <button type="button" class="an-btn an-btn-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="an-profile-img" style="background-image: url('<?php echo Template::theme_url("images/users/user5.jpg"); ?>');"></span>
                    <span class="an-user-name">John Smith</span>
                    <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                </button>
                <div class="dropdown-menu">
                    <p class="an-info-count">Profile Settings</p>
                    <ul class="an-profile-list">
                    <li><a href="#"><i class="icon-user"></i>My profile</a></li>
                    <li><a href="#"><i class="icon-envelop"></i>My inbox</a></li>
                    <li><a href="#"><i class="icon-calendar-check"></i>Calendar</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#"><i class="icon-lock"></i>Lock screen</a></li>
                    <li><a href="#"><i class="icon-download-left"></i>Log out</a></li>
                    </ul>
                </div>
                </div>
            </div> <!-- end .AN-PROFILE-SETTINGS -->
            </div> <!-- end .AN-TOPBAR-RIGHT-PART -->
        </header> <!-- end .AN-HEADER -->

        <div class="an-page-content">
            
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

                <div class="an-sidebar-widgets">
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
                </div> <!-- end .AN-SIDEBAR-WIDGETS -->

                <div class="an-sidebar-nav">
                    <ul class="an-main-nav">
                    <li class="an-nav-item current active">
                        <a class=" js-show-child-nav" href="#">
                        <i class="icon-chart-stock"></i>
                        <span class="nav-title">Dashboard
                            <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                        </span>
                        </a>

                        <ul class="an-child-nav js-open-nav">
                        <li><a href="index-2.html">Version 1</a></li>
                        <li><a href="index_2.html">Version 2</a></li>
                        <li><a href="index_3.html">Version 3</a></li>
                        </ul>
                    </li>
                    <li class="an-nav-item">
                        <a class=" js-show-child-nav" href="#">
                        <i class="icon-squares"></i>
                        <span class="nav-title">UI Layouts
                            <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                        </span>
                        </a>
                        <ul class="an-child-nav js-open-nav">
                        <li><a href="ui-basics.html">Basic Ui</a></li>
                        <li><a href="ui-buttons.html">Buttons</a></li>
                        <li><a href="ui-tabs.html">Tabs</a></li>
                        <li><a href="ui-accordions.html">Accordions</a></li>
                        <li><a href="ui-portlets.html">Portlets</a></li>
                        <li><a href="ui-sweetalerts.html">Sweet Alerts</a></li>
                        <li><a href="ui-social-icons.html">Social Icons</a></li>
                        <li><a href="ui-typography.html">Typography</a></li>
                        <li><a href="ui-modals.html">Modals</a></li>
                        <li><a href="ui-notifications.html">Notifications</a></li>
                        </ul>
                    </li>

                    <li class="an-nav-item">
                        <a class=" js-show-child-nav" href="#">
                        <i class="icon-grid"></i>
                        <span class="nav-title">Components
                            <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                        </span>
                        </a>
                        <ul class="an-child-nav js-open-nav">
                        <li><a href="component-select.html">Select Box</a></li>
                        <li><a href="component-switch.html">LC Switch</a></li>
                        <li><a href="component-bs-switch.html">Bootstrap switch</a></li>
                        <li><a href="component-clipboard.html">Clipboard</a></li>
                        <li><a href="component-datetime.html">Date Time Picker</a></li>
                        <li><a href="component-range.html">Ion Range Slider</a></li>
                        <li><a href="component-tags.html">Tags Input</a></li>
                        </ul>
                    </li>
                    <li class="an-nav-item">
                        <a class="" href="forms.html">
                        <i class="icon-setting"></i>
                        <span class="nav-title">Forms</span>
                        </a>
                    </li>

                    <li class="an-nav-item">
                        <a class=" js-show-child-nav" href="#">
                        <i class="icon-board-list"></i>
                        <span class="nav-title">Tables
                            <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                        </span>
                        </a>
                        <ul class="an-child-nav js-open-nav">
                        <li><a href="tables-basic.html">Basic tables</a></li>
                        <li><a href="tables-bootstrap.html">Bootstrap tables</a></li>
                        </ul>
                    </li>

                    <li class="an-nav-item">
                        <a class="" href="charts.html">
                        <i class="icon-chart"></i>
                        <span class="nav-title">Charts</span>
                        </a>
                    </li>

                    <li class="an-nav-item">
                        <a class="" href="maps.html">
                        <i class="icon-marker"></i>
                        <span class="nav-title">Maps</span>
                        </a>
                    </li>

                    <li class="an-nav-item">
                        <a class="" href="inbox.html">
                        <i class="icon-chat-o"></i>
                        <span class="nav-title">Inbox <span class="an-arrow-nav count">3</span></span>
                        </a>
                    </li>

                    <li class="an-nav-item">
                        <a class=" js-show-child-nav" href="#">
                        <i class="icon-book"></i>
                        <span class="nav-title">App page
                            <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                        </span>
                        </a>
                        <ul class="an-child-nav js-open-nav">
                        <li><a href="page-chats.html">Chat Layout</a></li>
                        <li><a href="page-profile.html">Profile Page</a></li>
                        <li><a href="page-profile-setting.html">Profile Setting</a></li>
                        <li><a href="page-login.html">Login Form</a></li>
                        <li><a href="page-signup.html">Register Form</a></li>
                        <li><a href="page-contact-us.html">Contact Us</a></li>
                        <li><a href="page-about-us.html">About Us</a></li>
                        <li><a href="page-pricing.html">Pricing Table</a></li>
                        <li><a href="page-portfolio.html">Portfolio</a></li>
                        <li><a href="page-blog.html">Blog List</a></li>
                        <li><a href="page-blog-post.html">Blog Post </a></li>
                        <li><a href="page-construction.html">Comming soon</a></li>
                        <li><a href="page-404.html">404 Page</a></li>
                        </ul>
                    </li>

                    <li class="an-nav-item">
                        <a class=" js-show-child-nav" href="#">
                        <i class="icon-dot-vertical"></i>
                        <span class="nav-title">Starter page
                            <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                        </span>
                        </a>
                        <ul class="an-child-nav js-open-nav">
                        <li><a href="layout-default.html">Layout Default</a></li>
                        <li><a href="layout-boxed.html">Layout Boxed</a></li>
                        <li><a href="layout-fixed-header.html">Layout Fixed Header</a></li>
                        <li><a href="layout-minimal-header.html">Layout Minimal Header</a></li>
                        <li><a href="layout-without-breadcrumb.html">Layout Without Breadcrumb</a></li>
                        <li><a href="layout-white-bg.html">Layout White Bg</a></li>
                        </ul>
                    </li>

                    <li class="an-nav-item">
                        <a class=" js-show-child-nav" href="#">
                        <i class="icon-dot-horizontal"></i>
                        <span class="nav-title">Sidebar layouts
                            <span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
                        </span>
                        </a>
                        <ul class="an-child-nav js-open-nav">
                        <li><a href="layout-sidebar-default.html">Default Sidebar</a></li>
                        <li><a href="layout-sidebar-hidden.html">Hidden Sidebar</a></li>
                        </ul>
                    </li>
                    </ul> <!-- end .AN-MAIN-NAV -->
                </div> <!-- end .AN-SIDEBAR-NAV -->
            </div> <!-- end .AN-SIDEBAR-NAV -->

            <div class="an-page-content">