<?php if (! $this->input->is_ajax_request()) : ?>
<div class="an-body-topbar wow fadeIn">
	<div class="an-page-title">
		<h2><?php echo lang('ad_tm_org_users') . ': ' . $organization->name ?></h2>
	</div>

	<div class="pull-right">
		<button class="an-btn an-btn-success mb-open-modal" data-url="<?php echo site_url('admin/team/invite')?>"><?php echo lang('ad_tm_invite_user') ?></button>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->


<div class="an-single-component with-shadow">
	<div class="an-component-body">
		<div class="an-bootstrap-custom-tab">
			<div class="an-tab-control">
				<!-- Nav tabs -->
				<ul class="nav nav-tabs text-left" role="tablist">
					<li class='active'>
						<a href="<?php echo site_url('admin/team') ?>">
							<?php echo lang('ad_tm_team') ?>
						</a>
					</li>
					<li>
						<a href="<?php echo site_url('admin/roles') ?>">
							<?php echo lang('ad_tm_roles') ?>
						</a>
					</li>
				</ul>
			</div>
			<div class="tab-content" style="padding: 20px 0 0 0">
				<div role="tabpanel" class="tab-pane active">
					<div class="an-tab-control">
						<!-- Nav tabs -->
						<ul class="nav nav-tabs text-left" role="tablist">
						<?php foreach (['all', 'disabled', 'by_role'] as $type) : ?>
							<li role="presentation" class="<?php if ($type == $this->input->get('type') || (is_null($this->input->get('type')) && $type == 'all')) echo 'active' ?>">
								<a href="<?php echo $type == 'by_role' ? '#' : site_url('admin/team?') . http_build_query(['type' => $type]) ?>" <?php echo $type == 'by_role' ? 'id="toggle_dropdown" style="cursor: pointer;"' : '' ?>>
									<?php echo lang('ad_tm_tab_' . $type) . ($type == 'by_role' ? '&nbsp;<i class="ion-arrow-down-b"></i>' : '') ?>
								</a>
								<?php if ($type == 'by_role') : ?>
								<div class="dropdown-menu right-align">
									<ul class="an-basic-list">
									<?php foreach ($roles as $role) : ?>
										<li><a href="<?php echo site_url('admin/team?') . http_build_query(['type' => $type, 'role_id' => $role->role_id]) ?>" style="padding: 3px 15px; color: inherit; border: none;"><?php e($role->name) ?></a></li>
									<?php endforeach ?>
									</ul>
								</div>
								<?php endif ?>
							</li>
						<?php endforeach ?>
						</ul>
					</div>
					<div class="tab-content" style="padding: 20px 0 0 0">
						<div role="tabpanel" class="tab-pane fade in active">
							<div class="an-user-lists tables messages">
								<div class="list-title">
									<h6 class="basis-20">
										<?php echo lang('ad_tm_full_name') ?>
									</h6>
									<h6 class="basis-20"><?php echo lang('ad_tm_title') ?></h6>
									<h6 class="basis-20"><?php echo lang('ad_tm_email') ?></h6>
									<h6 class="basis-10"><?php echo lang('ad_tm_role') ?></h6>
									<h6 class="basis-20"><?php echo lang('ad_tm_last_login') ?></h6>
									<h6 class="basis-10"><?php echo lang('ad_tm_status') ?></h6>
								</div>

								<div class="an-list-body">
								<?php if (empty($users_list['data'])) : ?>
									<div class="list-user-single">
										<div class="list-date" style="width: 100%">
											<p class="text-center"><?php echo lang('ad_tm_no_users') ?></p>
										</div>
									</div>
								<?php endif ?>

								<?php foreach ($users_list['data'] as $user) : ?>
									<div class="list-user-single edit-user mb-open-modal" style="cursor: pointer;" data-id="edit-user" data-url="<?php echo site_url('admin/team/edit_user/' . $user->user_id) ?>">
										<div class="list-name basis-20">
											<!--span class="an-custom-checkbox">
												<input id="check-40" type="checkbox">
												<label for="check-40"></label>
											</span-->
											<a><?php e($user->first_name . ' ' . $user->last_name) ?></a>
										</div>
										<div class="list-text basis-20">
											<p><?php e($user->title) ?></p>
										</div>
										<div class="list-date email approve basis-20">
											<p><?php e($user->email) ?></p>
										</div>
										<div class="list-text basis-10">
											<p><?php e($user->role_name) ?></p>
										</div>
										<div class="list-date basis-20">
											<p><?php e(display_time($user->last_login)) ?></p>
										</div>
										<div class="list-state basis-10 text-right">
											<span class="msg-tag <?php echo $user->enabled == 1 ? 'read' : 'spam' ?>"><?php echo $user->enabled == 1 ? 'enabled' : 'disabled' ?></span>
										</div>
									</div> <!-- end .USER-LIST-SINGLE -->
								<?php endforeach ?>
								</div>
							</div>

							<div class="an-pagination-container">
								<p class="result-info"><?php if (! empty($users_list['result'])) echo $users_list['result'] ?></p>
								<?php if (! empty($users_list['pagination'])) echo $users_list['pagination'] ?>
							</div>
						</div><!-- end .TAB-PANE -->
					</div>
				</div><!-- end .TAB-PANE -->
			</div>
		</div>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>





<div class="modal fade" id="inviteModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<script>
	var INVITE_USER_URL = '<?php echo site_url('admin/team/invite')?>';
</script>
<?php else : ?>
<div class="an-user-lists tables messages">
	<div class="list-title">
		<h6 class="basis-30">
			<!--span class="an-custom-checkbox">
				<input id="check-11" type="checkbox">
				<label for="check-11"></label>
			</span-->
			<?php echo lang('ad_tm_full_name') ?>
		</h6>
		<h6 class="basis-30"><?php echo lang('ad_tm_email') ?></h6>
		<h6 class="basis-10"><?php echo lang('ad_tm_role') ?></h6>
		<h6 class="basis-20"><?php echo lang('ad_tm_last_login') ?></h6>
		<h6 class="basis-10"><?php echo lang('ad_tm_status') ?></h6>
	</div>

	<div class="an-lists-body an-customScrollbar ps-container ps-theme-default ps-active-y">
	<?php if (empty($users_list['data'])) : ?>
		<div class="list-user-single">
			<div class="list-date" style="width: 100%">
				<p class="text-center"><?php echo lang('ad_tm_no_users') ?></p>
			</div>
		</div>
	<?php endif ?>

	<?php foreach ($users_list['data'] as $user) : ?>
		<div class="list-user-single edit-user" style="cursor: pointer;" data-id="<?php e($user->user_id) ?>" data-edit-user-url="<?php echo site_url('admin/team/edit_user/' . $user->user_id) ?>">
			<div class="list-name basis-30">
				<!--span class="an-custom-checkbox">
					<input id="check-40" type="checkbox">
					<label for="check-40"></label>
				</span-->
				<a><?php e($user->first_name . ' ' . $user->last_name) ?></a>
			</div>
			<div class="list-date email approve basis-30">
				<p><?php e($user->email) ?></p>
			</div>
			<div class="list-text basis-10">
				<p><?php e($user->role_name) ?></p>
			</div>
			<div class="list-date basis-20">
				<p><?php e(display_time($user->last_login)) ?></p>
			</div>
			<div class="list-state basis-10 text-right">
				<span class="msg-tag <?php echo $user->enabled == 1 ? 'read' : 'spam' ?>"><?php echo $user->enabled == 1 ? 'enabled' : 'disabled' ?></span>
			</div>
		</div> <!-- end .USER-LIST-SINGLE -->
	<?php endforeach ?>
	</div>
</div>

<div class="an-pagination-container">
	<p class="result-info"><?php if (! empty($users_list['result'])) echo $users_list['result'] ?></p>
	<?php if (! empty($users_list['pagination'])) echo $users_list['pagination'] ?>
</div>
<?php endif; ?>