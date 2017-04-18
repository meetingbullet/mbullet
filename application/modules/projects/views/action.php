<div id="board" data-drag-drop-url="<?php echo base_url('/projects/sort_action/' . $project_key) ?>" data-refresh-url="<?php echo base_url('/projects/get_action_board_data/' . $project_key) ?>">
	<div id="loading"></div>
	<div class="col-md-3 status" id="open">
		<label><?php echo lang('pj_open_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['open'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<?php echo $item->action_key ?>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-3 status" id="inprogress">
		<label><?php echo lang('pj_inprogress_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['inprogress'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<?php echo $item->action_key ?>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-3 status" id="ready">
		<label><?php echo lang('pj_ready_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['ready'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<?php echo $item->action_key ?>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-3 status" id="resolved">
		<label><?php echo lang('pj_resolved_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['resolved'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<?php echo $item->action_key ?>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
</div>