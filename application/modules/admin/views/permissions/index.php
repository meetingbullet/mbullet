<?php
	$can_create = has_permission('Permissions.Team.Create');
	$can_edit = has_permission('Permissions.Team.Edit');
	$can_delete = has_permission('Permissions.Team.Edit');
	$can_create = true;
	$divide = 5 * round(20/(count($roles) + 1));
	if ($divide < 10) {
		$divide = 10;
	}
?>




<div class="an-body-topbar wow fadeIn">
	<div class="an-page-title">
		<h2><?php e(lang('pm_permissions'))?></h2>
	</div>
	<?php if ($can_create): ?>
	<div class="pull-right">
		<button class="an-btn an-btn-primary" id="save-permissions">
			<?php echo lang('pm_save_permission') ?>
		</button>
	</div>
	<?php endif; ?>
</div>
<div class="an-single-component with-shadow">
	<div class="an-component-body" style="display:block;overflow-x:auto;">
		<div class="an-bootstrap-custom-tab" >
			<div class="an-tab-control">
				<!-- Nav tabs -->
				<ul class="nav nav-tabs text-left" role="tablist">
					<li>
						<a href="<?php echo site_url('admin/team') ?>">
							<?php echo lang('pm_team') ?>
						</a>
					</li>
					<li >
						<a href="<?php echo site_url('admin/roles') ?>">
							<?php echo lang('pm_roles') ?>
						</a>
					</li>
					<li class="active">
						<a href="<?php echo site_url('admin/permissions') ?>">
							<?php echo lang('pm_permissions') ?>
						</a>
					</li>
				</ul>
			</div>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active">
					<div id="permission-list" >
						<div class="an-component-body">

								<h4><?php e(lang('pm_manage_role'))?></h4>
								<blockquote style="font-size:14.5px">
								<p><?php e(lang('pm_manage_role_description'))?></p>
								</blockquote>
						
							<div class="an-user-lists tables messages">
								<div class="list-title">
									<h6 class="basis-<?php echo 2 * $divide; ?> "><?php e(lang('pm_role_to_edit'))?>
									<?php foreach ($roles as $role): ?>
										<h6 class="basis-<?php echo $divide; ?> text-center" style="text-align:center"><strong><?php echo $role->name; ?></strong></h6>
									<?php endforeach; ?>
								</div>

								<div class="an-lists-body an-customScrollbar ps-container ps-theme-default" style="max-height:none">
								<?php foreach ($roles as $manage_role): ?>
									<div class="list-user-single">
										<div class="list-name basis-<?php echo 2 * $divide; ?> text-center">
											<strong><?php e($manage_role->name)?></strong>
										</div>
										<?php foreach($roles as $role): ?>
											<div class="list-number-users basis-<?php echo $divide; ?> text-center">
												<?php 
												if( $role->is_public == 1 && $role->organization_id == null) {
													echo "<input type='checkbox' value='role-" . $role->role_id . "-role-" . $manage_role->role_id . "' checked disabled >";
												} else if( $manage_role->is_public == 1 && $manage_role->organization_id == null) {
													echo "<input type='checkbox' value='role-" . $role->role_id . "-role-" . $manage_role->role_id . "' disabled >";
												} else {
													$check = false;
													foreach ($relations as $relation) {
														if ($relation->role_id == $role->role_id && $relation->manage_role_id == $manage_role->role_id) {
															$check = true;
															break;
														}
													}
													if ($check) {
														echo "<input type='checkbox' value='role-" . $role->role_id . "-role-" . $manage_role->role_id . "' checked>";
													} else {
														echo "<input type='checkbox' value='role-" . $role->role_id . "-role-" . $manage_role->role_id . "'>";
													}
												}
												?>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endforeach; ?>
								</div>
							</div>
						</div>
						<div class="an-component-body">
								<h4><?php e(lang('pm_permissions'))?></h4>
								<blockquote style="font-size:14.5px">
									<p><?php e(lang('pm_permission_table_description'))?></p>
								</blockquote>
							<div class="an-user-lists tables messages">
								<div class="list-title">
									<h6 class="basis-<?php echo 2 * $divide; ?>"><?php e(lang('pm_permissions'))?></h6>
									<?php foreach ($roles as $role): ?>
										<h6 class="basis-<?php echo $divide; ?> text-center" style="text-align:center"><strong><?php echo $role->name; ?></strong></h6>
									<?php endforeach; ?>
								</div>
								<div class="an-lists-body an-customScrollbar ps-container ps-theme-default" style="max-height:none">
									<?php foreach ($permissions as $permission): ?>
										<div class="list-user-single">
											<div class="list-name basis-<?php echo 2 * $divide; ?>">
											<strong><?php e($permission->name)?></strong>
											</div>
											<?php foreach($roles as $role): ?>
												<div class="list-number-users basis-<?php echo $divide; ?> text-center">
													<?php 
													if ($role->is_public == 1) {
														echo "<input type='checkbox' value='role-" . $role->role_id . "-permission-" . $permission->permission_id . "' checked disabled >";
													} else {
														$check = false;
														foreach ($role_to_permission_relations as $role_to_permission_relation) {
															if ($role_to_permission_relation->role_id == $role->role_id && $role_to_permission_relation->permission_id == $permission->permission_id) {
																$check = true;
																break;
															}
														}
														if ($check) {
															echo "<input type='checkbox' value='role-" . $role->role_id . "-permission-" . $permission->permission_id . "' checked>";
														} else {
															echo "<input type='checkbox' value='role-" . $role->role_id . "-permission-" . $permission->permission_id . "'>";
														}
													}
													?>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>
<style>

@media only screen and (max-width: 767px) {
    .an-user-lists .list-user-single {
		flex-direction: unset;
		width
	}
	.an-user-lists .list-title {
		display: flex;
	}
	#permission-list{
		width: <?php echo (90*(count($roles)+1)) . "px"; ?>;
	}
	.an-user-lists .list-user-single > div {
		width: auto;
	}
	.an-user-lists .list-user-single .list-number-users {
		width: <?php echo $divide . "%" ?>;
	}
	 .an-user-lists .list-user-single .list-name {
		flex-direction: column;
		width: <?php echo $divide*2 . "%" ?>;
		text-align: left;
	} 
	.an-component-body {
		overflow-x: auto;
	}
}
</style>