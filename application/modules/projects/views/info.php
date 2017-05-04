<?php
$label = [
	'open' => 'read',
	'inprogress' => 'draft',
	'in-progress' => 'draft',
	'ready' => 'spam',
	'ready-for-review' => 'spam',
	'resolved' => 'unread'
];
?>
<div class="an-single-component with-shadow">
	<div class="an-component-header">
		<h6><?php e(lang('pj_detail_tab_info_table_all_actions')) ?></h6>
		<div class="component-header-right">
			<div class="an-settings-button">
				<a data-toggle="modal" data-target="#bigModal" id="add_action" data-add-action-url="<?php echo base_url('/action/create/' . $project_key) ?>" class="only-hover-setting circle" href="#"><i class="icon-plus"></i></a>
			</div>
		</div>
	</div>
	<div class="an-component-body padding20">
		<div class="an-user-lists user-stats">
			<div class="list-title">
				<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
				<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
				<h6 class="basis-30"><?php e(lang('pj_point_defined')) ?></h6>
				<h6 class="basis-30"><?php e(lang('pj_point_used')) ?></h6>
				<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
			</div>

			<div class="an-lists-body an-customScrollbar ps-container ps-theme-default">

			<?php if (is_array($lists['actions']) && count($lists['actions']) > 0) : ?>
				<?php foreach ($lists['actions'] as $item) : ?>

				<div class="list-user-single">
					<div class="list-date number basis-30">
						<a href="<?php e("/action/{$item->action_key}") ?>"><?php e($item->action_key) ?></a>
					</div>
					<div class="list-name basis-50">
						<a href="<?php e("/action/{$item->action_key}") ?>"><?php e($item->name) ?></a>
					</div>
					<div class="list-name basis-30">
						<?php e($item->point_value) ?>
					</div>
					<div class="list-name basis-30">
						<?php e($item->point_used) ?>
					</div>
					<div class="list-action basis-20">
						<span class="msg-tag <?php echo $label[$item->status] ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
					</div>
				</div> <!-- end .USER-LIST-SINGLE -->

				<?php endforeach ?>
			<?php else : ?>
				<div class="list-user-single">
					<div class="list-text basis-30">
					</div>
					<div class="list-date email approve basis-40">
						<?php e(lang('pj_no_action')) ?>
					</div>
					<div class="list-text basis-30">
					</div>
				</div>
			<?php endif ?>
			</div> <!-- end .AN-LISTS-BODY -->
		</div>

		<?php if (! empty($paginations['actions'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['actions'] ?>
		</div>
		<?php endif ?>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>

<div class="an-single-component with-shadow">
	<div class="an-component-header">
		<h6><?php e(lang('pj_detail_tab_info_table_all_steps')) ?></h6>
	</div>
	<div class="an-component-body padding20">
		<div class="an-user-lists user-stats">
			<div class="list-title">
				<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
				<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
				<h6 class="basis-30"><?php e(lang('pj_point_used')) ?></h6>
				<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
			</div>

			<div class="an-lists-body an-customScrollbar ps-container ps-theme-default">

			<?php if (is_array($lists['steps']) && count($lists['steps']) > 0) : ?>
				<?php foreach ($lists['steps'] as $item) : ?>

				<div class="list-user-single">
					<div class="list-date number basis-30">
						<a href="<?php e("/step/{$item->step_key}") ?>"><?php e($item->step_key) ?></a>
					</div>
					<div class="list-name basis-50">
						<a href="<?php e("/step/{$item->step_key}") ?>"><?php e($item->name) ?></a>
					</div>
					<div class="list-name basis-30">
						<?php e($item->point_used) ?>
					</div>
					<div class="list-action basis-20">
						<span class="msg-tag <?php echo $label[$item->status] ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
					</div>
				</div> <!-- end .USER-LIST-SINGLE -->

				<?php endforeach ?>
			<?php else : ?>
				<div class="list-user-single">
					<div class="list-text basis-30">
					</div>
					<div class="list-date email approve basis-40">
						<?php e(lang('pj_no_step')) ?>
					</div>
					<div class="list-text basis-30">
					</div>
				</div>
			<?php endif ?>
			</div> <!-- end .AN-LISTS-BODY -->
		</div>

		<?php if (! empty($paginations['steps'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['steps'] ?>
		</div>
		<?php endif ?>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>

<div class="an-single-component with-shadow">
	<div class="an-component-header">
		<h6><?php e(lang('pj_detail_tab_info_table_all_tasks')) ?></h6>
	</div>
	<div class="an-component-body padding20">
		<div class="an-user-lists user-stats">
			<div class="list-title">
				<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
				<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
				<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
			</div>

			<div class="an-lists-body an-customScrollbar ps-container ps-theme-default">

			<?php if (is_array($lists['tasks']) && count($lists['tasks']) > 0) : ?>
				<?php foreach ($lists['tasks'] as $item) : ?>

				<div class="list-user-single">
					<div class="list-date number basis-30">
						<a href="<?php e("/task/{$item->task_key}") ?>"><?php e($item->task_key) ?></a>
					</div>
					<div class="list-name basis-50">
						<a href="<?php e("/task/{$item->task_key}") ?>"><?php e($item->name) ?></a>
					</div>
					<div class="list-action basis-20">
						<span class="msg-tag <?php echo $label[$item->status] ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
					</div>
				</div> <!-- end .USER-LIST-SINGLE -->

				<?php endforeach ?>
			<?php else : ?>
				<div class="list-user-single">
					<div class="list-text basis-30">
					</div>
					<div class="list-date email approve basis-40">
						<?php e(lang('pj_no_task')) ?>
					</div>
					<div class="list-text basis-30">
					</div>
				</div>
			<?php endif ?>
			</div> <!-- end .AN-LISTS-BODY -->
		</div>

		<?php if (! empty($paginations['tasks'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['tasks'] ?>
		</div>
		<?php endif ?>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>