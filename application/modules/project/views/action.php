<div id="board" data-add-action-url="<?php echo base_url('/action/create/' . $project_key) ?>" data-drag-drop-url="<?php echo base_url('/project/sort_action/' . $project_key) ?>" data-refresh-url="<?php echo base_url('/project/get_action_board_data/' . $project_key) ?>">
	<div id="loading"></div>
	<div class="col-md-3 status" id="open">
		<label><?php echo lang('pj_open_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['open'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<address>
						<strong><?php e($item->name) ?></strong><br/>
						<a href="<?php e('/action/' . $item->action_key) ?>"><img style="width: 24px; height: auto;" src="<?php echo $item->avatar_url ?>" class="img-circle"/> <?php echo $item->action_key ?> <i class="ion-edit"></i></a>
					</address>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button data-toggle="modal" data-target="#bigModal"><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-3 status" id="inprogress">
		<label><?php echo lang('pj_inprogress_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['inprogress'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<address>
						<strong><?php e($item->name) ?></strong><br/>
						<a href="<?php e('/action/' . $item->action_key) ?>"><img style="width: 24px; height: auto;" src="<?php echo $item->avatar_url ?>" class="img-circle"/> <?php echo $item->action_key ?> <i class="ion-edit"></i></a>
					</address>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button data-toggle="modal" data-target="#bigModal"><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-3 status" id="ready">
		<label><?php echo lang('pj_ready_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['ready'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<address>
						<strong><?php e($item->name) ?></strong><br/>
						<a href="<?php e('/action/' . $item->action_key) ?>"><img style="width: 24px; height: auto;" src="<?php echo $item->avatar_url ?>" class="img-circle"/> <?php echo $item->action_key ?> <i class="ion-edit"></i></a>
					</address>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button data-toggle="modal" data-target="#bigModal"><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
	<div class="col-md-3 status" id="resolved">
		<label><?php echo lang('pj_resolved_label') ?></label>
		<div class="actions">
			<div class="items">
			<?php foreach ($actions['resolved'] as $item) : ?>
				<div class="item" data-action-id="<?php e($item->action_id) ?>">
					<address>
						<strong><?php e($item->name) ?></strong><br/>
						<a href="<?php e('/action/' . $item->action_key) ?>"><img style="width: 24px; height: auto;" src="<?php echo $item->avatar_url ?>" class="img-circle"/> <?php echo $item->action_key ?> <i class="ion-edit"></i></a>
					</address>
				</div>
			<?php endforeach ?>
			</div>
			<div class="add-action">
				<button data-toggle="modal" data-target="#bigModal"><i class="ion-ios-plus-outline"></i></button>
			</div>
		</div>
	</div>
</div>
<!-- Modal -->
<div class="modal fade" id="bigModal" tabindex="-1" role="dialog" aria-labelledby="bigModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>