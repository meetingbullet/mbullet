		<header class="an-header wow fadeInDown" style="visibility: visible; animation-name: fadeInDown;">
			<div class="an-topbar-left-part">
				<h3 class="an-logo-heading">
					<a class="an-logo-link" href="<?php echo site_url(DEFAULT_LOGIN_LOCATION); ?>"><img src="<?php echo base_url('assets/images/logo-white.svg'); ?>" alt="<?php echo $this->settings_lib->item('site.title'); ?> " width="87" height="31"></a>
				</h3>

				<div class="topbar-action">
					<div class="btn-group">
						<button type="button" class="an-btn an-btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php e($current_user->current_project_name) ?> <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<?php if ($current_user->projects && count($current_user->projects)): 
									foreach ($current_user->projects AS $project):
							?>
							
							<li<?php echo $project->project_id == $current_user->current_project_id ? ' class="active"' : '' ?>>
								<a href="<?php echo site_url('project/' . $project->cost_code) ?>"><?php e($project->name)?></a>
							</li>
							<?php endforeach; ?>
							<?php endif; ?>

							<?php if (has_permission('Project.Create')) : ?>
							<?php if ($current_user->projects && count($current_user->projects)): ?>
							<li role="separator" class="divider"></li>
							<?php endif; ?>
							<li>
								<a	href="#" 
									class='mb-open-modal' 
									data-modal-id="create-project-modal"
									data-url="<?php echo site_url('project/create')?>" 
								>
									<i class="ion-ios-plus-outline"></i> <?php e(lang('create_project'))?>
								</a>
							</li>
							<?php endif; ?>
						</ul>
					</div>

					<!-- button class="an-btn an-btn-primary"><i class="ion-calendar"></i> <?php e(lang('my_calendar')) ?></button-->
				</div>

				<!--<button class="an-btn an-btn-icon toggle-button js-toggle-sidebar">
					<i class="icon-list"></i>
				</button>
				<form class="an-form" action="#">
					<div class="an-search-field topbar">
					<input class="an-form-control" type="text" placeholder="Search...">
					<button class="an-btn an-btn-icon" type="submit">
						<i class="icon-search"></i>
					</button>
					</div>
				</form>-->
			</div> <!-- end .AN-TOPBAR-LEFT-PART -->

			<div class="an-topbar-right-part">
			<div class="an-notifications">
				<div class="btn-group an-notifications-dropown notifications">
				<button type="button" class="an-btn an-btn-icon dropdown-toggle js-has-new-notification" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="ion-ios-bell-outline"></i>
				</button>
				<!--<div class="dropdown-menu">
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
					<div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 0px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div></div></div> 
					<div class="an-info-show-all-btn">
					<a class="an-btn an-btn-transparent fluid rounded uppercase small-font" href="#">Show all</a>
					</div>
				</div>-->
				</div>
			</div> <!-- end .AN-NOTIFICATION -->

			<div class="an-messages">
				<div class="btn-group an-notifications-dropown messages">
				<button type="button" class="an-btn an-btn-icon dropdown-toggle js-has-new-messages" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="ion-ios-email-outline"></i>
				</button>
				<!--<div class="dropdown-menu">
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
					<div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;"><div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps-scrollbar-y-rail" style="top: 0px; right: 0px;"><div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div></div></div>

					<div class="an-info-show-all-btn">
					<a class="an-btn an-btn-transparent fluid rounded uppercase small-font" href="#">Show all</a>
					</div>
				</div>-->
				</div>
			</div> <!-- end .AN-MESSAGE -->

			<div class="an-settings">
				<div class="btn-group an-notifications-dropown settings">
					<button type="button" class="an-btn an-btn-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="ion-ios-gear-outline"></i>
					</button>
					<div class="dropdown-menu">
						<p class="an-info-count">Settings</p>
						<ul class="an-settings-list">
							<li><a href="<?php echo site_url('admin/settings'); ?>"><i class="ion-ios-settings"></i>Preferences</a></li>
							<li><a href="<?php echo site_url('admin/team'); ?>"><i class="ion-ios-people-outline"></i>Team</a></li>
							<li><a href="<?php echo site_url('admin/invites'); ?>"><i class="ion-ios-personadd-outline"></i>Invitations</a></li>
							<li><a href="<?php echo site_url('admin/billing'); ?>"><i class="ion-social-usd-outline"></i>Billing</a></li>
							<li><a href="<?php echo site_url('admin/auth'); ?>"><i class="ion-ios-locked-outline"></i>Authentication</a></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="an-profile-settings">
				<div class="btn-group an-notifications-dropown profile">
					<button type="button" class="an-btn an-btn-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="an-profile-img" style="background-image: url('<?php
						if (empty($current_user->avatar)) echo gravatar_url($current_user->email, 40);
						elseif (!filter_var($current_user->avatar, FILTER_VALIDATE_URL) === false)	echo $current_user->avatar;
						else echo $current_user->avatar ? img_path() . 'users/' . $current_user->avatar : img_path() . 'default_avatar.png'; 
						?>');"></span>
						<div class="user-info">
							<span class="an-user-name"><?php echo $current_user->first_name . ' ' . $current_user->last_name ?></span>
							<div class="rate">
								<span class="average-star">
									<?php 
										$counting_stars = $current_user->star;
										$all_star = 5;
										while ($all_star --) {

											if ($counting_stars-- > 0) {
												echo '<i class="ion-android-star"></i>';
											} else {
												echo '<i class="ion-android-star-outline"></i>';
											}
										}
									?>
								</span>
							</div>
							<div class="total-points"><?php e(sprintf(lang('xp_x'), empty($current_user->exp) ? 0 : $current_user->exp)) ?></div>
						</div>
						<span class="an-arrow-nav"><i class="icon-arrow-down"></i></span>
					</button>
					<div class="dropdown-menu">
						<p class="an-info-count">Profile</p>
						<ul class="an-profile-list">
							<li><a href="<?php e(site_url() . 'users/profile')?>"><i class="icon-user"></i>My profile</a></li>
							<!--<li><a href="#"><i class="icon-envelop"></i>My inbox</a></li>
							<li><a href="#"><i class="icon-calendar-check"></i>Calendar</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="#"><i class="icon-lock"></i>Lock screen</a></li>-->
							<li><a href="<?php e(site_url() . 'logout')?>"><i class="icon-download-left"></i><?php e(lang('us_logout'))?></a></li>
						</ul>
					</div>
				</div>
			</div> <!-- end .AN-PROFILE-SETTINGS -->
			</div> <!-- end .AN-TOPBAR-RIGHT-PART -->
		</header> <!-- end .AN-HEADER -->
