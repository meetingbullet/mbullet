<div class="panel panel-default">
	<div class="panel-heading"><?php e(lang('pj_detail_tab_info_table_actions')) ?></div>
	<div class="panel-body">
		<table class="table table-responsive table-striped table-hover">
			<thead>
				<tr>
					<th><?php e(lang('pj_detail_tab_info_table_label_key')) ?></th>
					<th><?php e(lang('pj_detail_tab_info_table_label_name')) ?></th>
					<th><?php e(lang('pj_detail_tab_info_table_label_status')) ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($lists['actions'] as $item) : ?>
				<tr>
					<th scope="row"><a href="<?php e("/project/{$project_key}/action/{$item->action_key}") ?>"><?php e($item->action_key) ?></a></th>
					<td><?php e($item->name) ?></td>
					<td><?php e($item->status) ?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
		<?php if (! empty($paginations['actions'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['actions'] ?>
		</div>
		<?php endif ?>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading"><?php e(lang('pj_detail_tab_info_table_steps')) ?></div>
	<div class="panel-body">
		<table class="table table-responsive table-striped table-hover">
			<thead>
				<tr>
					<th><?php e(lang('pj_detail_tab_info_table_label_key')) ?></th>
					<th><?php e(lang('pj_detail_tab_info_table_label_name')) ?></th>
					<th><?php e(lang('pj_detail_tab_info_table_label_status')) ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($lists['steps'] as $item) : ?>
				<tr>
					<th scope="row"><a href="<?php e("/project/{$project_key}/action/{$item->action_key}/step/{$item->step_key}") ?>"><?php e($item->step_key) ?></a></th>
					<td><?php e($item->name) ?></td>
					<td><?php e($item->status) ?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
		<?php if (! empty($paginations['steps'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['steps'] ?>
		</div>
		<?php endif ?>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading"><?php e(lang('pj_detail_tab_info_table_tasks')) ?></div>
	<div class="panel-body">
		<table class="table table-responsive table-striped table-hover">
			<thead>
				<tr>
					<th><?php e(lang('pj_detail_tab_info_table_label_key')) ?></th>
					<th><?php e(lang('pj_detail_tab_info_table_label_name')) ?></th>
					<th><?php e(lang('pj_detail_tab_info_table_label_status')) ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($lists['tasks'] as $item) : ?>
				<tr>
					<th scope="row"><a href="<?php e("/project/{$project_key}/action/{$item->action_key}/step/{$item->step_key}/task/{$item->task_key}") ?>"><?php e($item->task_key) ?></a></th>
					<td><?php e($item->name) ?></td>
					<td><?php e($item->status) ?></td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
		<?php if (! empty($paginations['tasks'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['tasks'] ?>
		</div>
		<?php endif ?>
	</div>
</div>