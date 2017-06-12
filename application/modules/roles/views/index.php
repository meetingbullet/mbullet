<?php 
$can_create = has_permission('Role.Team.Create');
$can_edit = has_permission('Role.Team.Edit');
$can_delete = has_permission('Role.Team.Delete');

?>

<div class="an-body-topbar wow fadeIn">
	<div class="an-page-title">
		<h2><?php e(lang('rl_roles'))?></h2>
	</div>
</div>

<div class="row">
	<div id="role-list" class="col-md-8">
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('rl_roles'))?></h6>
				<div class="component-header-right">
					<div class="an-settings-button">
						<?php if ($can_create): ?>
						<a 	href="<?php echo site_url('roles/create') ?>" 
							class="an-btn an-btn-icon setting circle mb-open-modal"
							style="line-height: 29px;"
						>
							<i class="ion-android-add" style="font-size: 18px;"></i>
						</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="an-component-body">
				<div class="an-user-lists">
					<div class="list-title">
						<h6 class="basis-20"><?php e(lang('rl_name'))?></h6>
						<h6 class="basis-50"><?php echo lang('rl_description') ?></h6>
						<h6 class="basis-10 text-center"><?php e(lang('rl_join_default'))?></h6>
						<h6 class="basis-20"><?php echo lang('rl_action') ?></h6>
					</div>

					<div class="an-lists-body an-customScrollbar ps-container ps-theme-default">
						<?php foreach ($roles as $role) :?>
						<div id="role-<?php e($role->role_id) ?>" class="list-user-single">
							<div class="list-name basis-20">
								<?php if ($can_edit && $role->role_id != $current_role_id): ?>
								<a href="<?php echo site_url('roles/edit/' . $role->role_id) ?>" class='mb-open-modal' data-modal-id="update-role-modal"><?php e($role->name)?></a>
								<?php else: ?>
								<?php e($role->name)?>
								<?php endif; ?>
							</div>
							<div class="list-description basis-50">
								<?php e($role->description)?>
							</div>
							<div class="list-join-default basis-10 text-center">
								<?php if ($role->join_default == 1): ?>
								<i class="ion-checkmark-circled"></i>
								<?php endif; ?>
							</div>
							<div class="list-action basis-20">
								<?php if ($can_edit && $role->role_id != $current_role_id): ?>
								<a href="<?php echo site_url('roles/edit/' . $role->role_id) ?>" class="an-btn an-btn-icon muted mb-open-modal" data-modal-id="update-role-modal">
									<i class="icon-setting"></i>
								</a>
								<?php endif; ?>

								<?php if ($can_delete): ?>
								<button class="an-btn an-btn-icon small muted danger mb-btn-delete-role" data-role-id="<?php e($role->role_id) ?>"><i class="icon-trash"></i></button>
								<?php endif; ?>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->
						<?php endforeach; ?>
					</div>
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div> <!-- end .AN-SINGLE-COMPONENT messages -->
		</div>
	</div>
</div>

<?php if ($can_create): ?>
	<script type="ajax" id="rolls-royce">
		<div class="list-user-single">
			<div class="list-name basis-20">
				<?php if ($can_edit): ?>
				<a href="<?php echo site_url('roles/edit/') ?>{{:role_id}}" class='mb-open-modal'>{{:name}}</a>
				<?php else: ?>
				{{:name}}
				<?php endif; ?>
			</div>
			<div class="list-name basis-50">
				{{:description}}
			</div>
			<div class="list-join-default basis-10 text-center">
			</div>
			<div class="list-action basis-20">
				<?php if ($can_edit): ?>
				<a href="<?php echo site_url('roles/edit/') ?>{{:role_id}}" class="an-btn an-btn-icon small mb-open-modal">
					<i class="ion-edit"></i>
				</a>
				<?php endif; ?>
			</div>
		</div> <!-- end .USER-LIST-SINGLE -->
	</script>
<?php endif; ?>
