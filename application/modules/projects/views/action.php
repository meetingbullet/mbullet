<div id="board">
	<div class="col-md-3 status" id="status_open">
		<label>Open</label>
		<div class="actions">
		<?php foreach ($actions['open'] as $item) : ?>
			<div class="item">
				<?php echo $item->action_key ?>
			</div>
		<?php endforeach ?>
		</div>
	</div>
	<div class="col-md-3 status" id="status_inprogress">
		<label>In-progress</label>
		<div class="actions">
		<?php foreach ($actions['inprogress'] as $item) : ?>
			<div class="item">
				<?php echo $item->action_key ?>
			</div>
		<?php endforeach ?>
		</div>
	</div>
	<div class="col-md-3 status" id="status_ready">
		<label>Ready for review</label>
		<div class="actions">
		<?php foreach ($actions['ready'] as $item) : ?>
			<div class="item">
				<?php echo $item->action_key ?>
			</div>
		<?php endforeach ?>
		</div>
	</div>
	<div class="col-md-3 status" id="status_resolved">
		<label>Resolved</label>
		<div class="actions">
		<?php foreach ($actions['resolved'] as $item) : ?>
			<div class="item">
				<?php echo $item->action_key ?>
			</div>
		<?php endforeach ?>
		</div>
	</div>
</div>