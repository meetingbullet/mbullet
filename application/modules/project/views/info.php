<?php
$label = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'ready' => 'label label-success label-bordered',
	'finished' => 'label label-info label-bordered',
	'resolved' => 'label label-success label-bordered'
];

$agenda_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
];
?>

<!-- 
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
		<div id="action-list" class="an-user-lists user-stats">
			<div class="list-title">
				<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
				<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
				<h6 class="basis-30"><?php e(lang('pj_point_defined')) ?></h6>
				<h6 class="basis-30"><?php e(lang('pj_point_used')) ?></h6>
				<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
			</div>

			<div class="an-lists-body">
			<?php if (is_array($lists['actions']) && count($lists['actions']) > 0) : ?>
				<?php foreach ($lists['actions'] as $item) : ?>

				<div data-action-id="<?php e($item->action_id) ?>" class="list-user-single">
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
						<span class="msg-tag label label-bordered label-<?php echo $item->status ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
					</div>
				</div> <!-- end .USER-LIST-SINGLE ->

				<?php endforeach ?>
			<?php else : ?>
				<div id="no-action" class="list-user-single">
					<div class="list-text basis-30">
					</div>
					<div class="list-date email approve basis-40">
						<?php e(lang('pj_no_action')) ?>
					</div>
					<div class="list-text basis-30">
					</div>
				</div>
			<?php endif ?>
			</div> <!-- end .AN-LISTS-BODY ->
		</div>

		<?php if (! empty($paginations['actions'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['actions'] ?>
		</div>
		<?php endif ?>
	</div> <!- end .AN-COMPONENT-BODY ->
</div>
-->

<div class="an-single-component with-shadow">
	<div class="an-component-header">
		<h6><?php e(lang('pj_detail_tab_info_table_all_meetings')) ?></h6>

		<div class="component-header-right">
			<div class="an-settings-button">
				<a	href="#" 
					class='only-hover-setting circle mb-open-modal' 
					data-modal-id="create-meeting-modal"
					data-url="<?php echo base_url('/meeting/create/' . $project_key) ?>" 
				>
					<i class="icon-plus"></i>
				</a>
			</div>
		</div>
	</div>
	<div class="an-component-body padding20">
		<div id="meeting-list" class="an-user-lists user-stats">
			<div class="list-title">
				<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
				<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
				<h6 class="basis-30"><?php e(lang('pj_point_used')) ?></h6>
				<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
			</div>

			<div class="an-lists-body">

			<?php if (is_array($lists['meetings']) && count($lists['meetings']) > 0) : ?>
				<?php foreach ($lists['meetings'] as $item) : ?>

				<div data-meeting-id="<?php e($item->meeting_id) ?>" class="list-user-single">
					<div class="list-date number basis-30">
						<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->meeting_key) ?></a>
					</div>
					<div class="list-name basis-50">
						<a href="<?php e("/meeting/{$item->meeting_key}") ?>"><?php e($item->name) ?></a>
					</div>
					<div class="list-name basis-30">
						<?php e($item->point_used) ?>
					</div>
					<div class="list-action basis-20">
						<span class="msg-tag label label-bordered label-<?php echo $item->status ?>"><?php e(str_replace('-', ' ', $item->status)) ?></span>
					</div>
				</div> <!-- end .USER-LIST-SINGLE -->

				<?php endforeach ?>
			<?php else : ?>
				<div id="no-meeting" class="list-user-single">
					<div class="list-text basis-30">
					</div>
					<div class="list-date email approve basis-40">
						<?php e(lang('pj_no_meeting')) ?>
					</div>
					<div class="list-text basis-30">
					</div>
				</div>
			<?php endif ?>
			</div> <!-- end .AN-LISTS-BODY -->
		</div>

		<?php if (! empty($paginations['meetings'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['meetings'] ?>
		</div>
		<?php endif ?>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>

<div class="an-single-component with-shadow">
	<div class="an-component-header">
		<h6><?php e(lang('pj_detail_tab_info_table_all_agendas')) ?></h6>
	</div>
	<div class="an-component-body padding20">
		<div class="an-user-lists user-stats">
			<div class="list-title">
				<h6 class="basis-30"><?php e(lang('pj_detail_tab_info_table_label_key')) ?></h6>
				<h6 class="basis-50"><?php e(lang('pj_detail_tab_info_table_label_name')) ?></h6>
				<h6 class="basis-20"><?php e(lang('pj_detail_tab_info_table_label_status')) ?></h6>
			</div>

			<div class="an-lists-body">

			<?php if (is_array($lists['agendas']) && count($lists['agendas']) > 0) : ?>
				<?php foreach ($lists['agendas'] as $item) : ?>

				<div data-agenda-id="<?php e($item->agenda_id) ?>" class="list-user-single">
					<div class="list-date number basis-30">
						<a href="<?php e("/agenda/{$item->agenda_key}") ?>"><?php e($item->agenda_key) ?></a>
					</div>
					<div class="list-name basis-50">
						<a href="<?php e("/agenda/{$item->agenda_key}") ?>"><?php e($item->name) ?></a>
					</div>
					<div class="list-action basis-20">
						<span class="msg-tag label label-bordered label-<?php echo $item->status ?>"><?php e(lang('pj_' . $item->status)) ?></span>
					</div>
				</div> <!-- end .USER-LIST-SINGLE -->

				<?php endforeach ?>
			<?php else : ?>
				<div class="list-user-single">
					<div class="list-text basis-30">
					</div>
					<div class="list-date email approve basis-40">
						<?php e(lang('pj_no_agenda')) ?>
					</div>
					<div class="list-text basis-30">
					</div>
				</div>
			<?php endif ?>
			</div> <!-- end .AN-LISTS-BODY -->
		</div>

		<?php if (! empty($paginations['agendas'])) : ?>
		<div class="an-pagination-container right">
			<?php echo $paginations['agendas'] ?>
		</div>
		<?php endif ?>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>

<script id="meeting-row" type="text">
	<div data-meeting-id="{{:meeting_id}}" class="list-user-single">
		<div class="list-date number basis-30">
			<a href="<?php echo site_url('meeting/') ?>{{:meeting_key}}">{{:meeting_key}}</a>
		</div>
		<div class="list-name basis-50">
			<a href="<?php echo site_url('meeting/') ?>{{:meeting_key}}">{{:name}}</a>
		</div>
		<div class="list-name basis-30">
			0.00
		</div>
		<div class="list-action basis-20">
			<span class="msg-tag label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</div>
	</div> <!-- end .USER-LIST-SINGLE -->
</script>