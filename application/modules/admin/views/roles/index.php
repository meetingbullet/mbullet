<?php 
$can_create = has_permission('Role.Team.Create');
$can_edit = has_permission('Role.Team.Edit');
$can_delete = has_permission('Role.Team.Delete');
?>

<div class="an-body-topbar wow fadeIn">
	<div class="an-page-title">
		<h2><?php e(lang('rl_roles'))?></h2>
	</div>

	<div class="pull-right">
		<?php if ($can_create): ?>
		<a 	href="<?php echo site_url('admin/roles/create') ?>" 
			class="an-btn an-btn-primary mb-open-modal"
		>
			<?php echo lang('rl_create_role') ?>
		</a>
		<?php endif; ?>
	</div>
</div>



<div class="an-single-component with-shadow">
	<div class="an-component-body">
		<div class="an-bootstrap-custom-tab">
			<div class="an-tab-control">
				<!-- Nav tabs -->
				<ul class="nav nav-tabs text-left" role="tablist">
					<li>
						<a href="<?php echo site_url('admin/team') ?>">
							<?php echo lang('rl_team') ?>
						</a>
					</li>
					<li class='active'>
						<a href="<?php echo site_url('admin/roles') ?>">
							<?php echo lang('rl_roles') ?>
						</a>
					</li>
					<li >
						<a href="<?php echo site_url('admin/permissions') ?>">
							<?php echo lang('rl_permissions') ?>
						</a>
					</li>
				</ul>
			</div>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane active">
					
					<div id="role-list" >
						<div class="an-component-body">
							<div class="an-user-lists tables messages">
								<div class="list-title">
									<h6 class="basis-20"><?php e(lang('rl_name'))?></h6>
									<h6 class="basis-40"><?php echo lang('rl_description') ?></h6>
									<h6 class="basis-10"><?php echo lang('rl_number_users') ?></h6>
									<h6 class="basis-10 text-center"><?php e(lang('rl_join_default'))?></h6>
									<h6 class="basis-20"><?php echo lang('rl_action') ?></h6>
								</div>

								<div class="an-lists-body an-customScrollbar ps-container ps-theme-default" style="max-height:none">
									<?php foreach ($roles as $role) :?>
									<div id="role-<?php e($role->role_id) ?>" class="list-user-single <?php echo $role->join_default == 1 ? 'div-join-default' : '' ?>">
										<div class="list-name basis-20">
											<?php if ($can_edit && $role->role_id != $current_role_id && $role->is_public == 0): ?>
											<a href="<?php echo site_url('admin/roles/create/' . $role->role_id) ?>" class='mb-open-modal' data-modal-id="update-role-modal"><?php e($role->name)?></a>
											<?php else: ?>
											<strong><?php e($role->name)?></strong>
											<?php endif; ?>
										</div>
										<div class="list-description basis-40">
											<?php e($role->description)?>
										</div>
										<div class="list-number-users basis-10">
											<?php e($role->number_users)?>
										</div>
										<div class="list-join-default basis-10 text-center">
											<?php if ($role->join_default == 1): ?>
											<i class="ion-checkmark-circled"></i>
											<?php endif; ?>
										</div>
										<div class="list-action basis-20">
											<?php if ($can_edit && $role->role_id != $current_role_id && $role->is_public == 0): ?>
											<a href="<?php echo site_url('admin/roles/create/' . $role->role_id) ?>" class="an-btn an-btn-icon muted mb-open-modal" data-modal-id="update-role-modal">
												<i class="icon-setting"></i>
											</a>
											<?php endif; ?>

											<?php if ($can_delete && $role->is_public == 0): ?>
											<button class="an-btn an-btn-icon small muted danger mb-btn-delete-role" data-role-id="<?php e($role->role_id) ?>"><i class="icon-trash"></i></button>
											<?php endif; ?>
										</div>
									</div> <!-- end .USER-LIST-SINGLE -->
									<?php endforeach; ?>
								</div>
							</div> <!-- end .AN-COMPONENT-BODY -->
						</div> <!-- end .AN-SINGLE-COMPONENT messages -->
					</div> <!-- / #role-list -->

				</div><!-- end .TAB-PANE -->
			</div>
		</div>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>

<?php if ($can_create): ?>
	<script type="ajax" id="rolls-royce">
		<div class="list-user-single" id="role-{{:role_id}}">
			<div class="list-name basis-20">
				<?php if ($can_edit): ?>
				<a href="<?php echo site_url('admin/roles/create/') ?>{{:role_id}}" class='mb-open-modal'>{{:name}}</a>
				<?php else: ?>
				<strong>{{:name}}</strong>
				<?php endif; ?>
			</div>
			<div class="list-name basis-40">
				{{:description}}
			</div>
			<div class="list-name basis-10">
				{{:number_users}}
			</div>
			<div class="list-join-default basis-10 text-center">
			</div>
			<div class="list-action basis-20">
				<?php if ($can_edit): ?>
				<a href="<?php echo site_url('admin/roles/create/') ?>{{:role_id}}" class="an-btn an-btn-icon muted mb-open-modal">
					<i class="icon-setting"></i>
				</a>
				<?php endif; ?>
				<?php if ($can_delete): ?>
				<button class="an-btn an-btn-icon small muted danger mb-btn-delete-role" data-role-id="{{:role_id}}"><i class="icon-trash"></i></button>
				<?php endif; ?>
			</div>
		</div> <!-- end .USER-LIST-SINGLE -->
	</script>
<?php endif; ?>
