<div class="init-project">
	<table class="table table-striped">
		<thead>
			<tr>
				<th colspan="2">Meeting</th>
				<th>Your Time (hours)</th>
				<th>Total Time (hours)</th>
				<th>As Owner</th>
				<th>As Guest</th>
				<th>Members</th>
				<th colspan="2">Project</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($data['meetings'] as $event_id => $meeting) : ?>
			<tr data-event-id="<?php echo $event_id ?>">
				<td class="action"><i class="delete-meeting ion-minus-circled"></i></td>
				<td><?php echo $meeting['name'] ?></td>
				<td><?php echo $meeting['in'] / 60 ?></td>
				<td><?php echo $meeting['in'] * (count($meeting['members']) + 1) / 60 ?></td>
				<td>
				<?php if (! empty($meeting['owner']['self'])) : ?>
					<i class="ion-checkmark"></i>
				<?php endif ?>
				</td>
				<td>
				<?php if (empty($meeting['owner']['self'])) : ?>
					<i class="ion-checkmark"></i>
				<?php endif ?>
				</td>
				<td><?php echo (count($meeting['members'])) ?></td>
				<td>
					<select class="an-form-control">
						<?php if(!empty($created_project)):?>
							<option value="<?php echo $created_project->project_id ?>" <?php if (! empty($meeting['project_id']) && $meeting['project_id'] == $created_project->project_id) echo 'selected' ?>><?php echo $created_project->name ?></option>
						<?php else:?>
							<option value="">Unspecified Project </option>
						<?php endif;?>
						<?php foreach ($projects as $project) : ?>
							<?php if($project->project_id != $created_project->project_id):?>
								<option value="<?php echo $project->project_id ?>" <?php if (! empty($meeting['project_id']) && $meeting['project_id'] == $project->project_id) echo 'selected' ?>><?php echo $project->name ?></option>
							<?php endif;?>
						<?php endforeach ?>
					</select>
				</td>
				<td class="action">
				<?php if (has_permission('Project.Create')) : ?>
					<i data-modal-id="init-create-project-modal" data-url="<?php echo site_url('project/create')?>" class="mb-open-modal add-project ion-plus-circled"></i>
				<?php endif ?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</div>
<script>
$('.init-project select').change();
if (! $('.init-project select option').length) {
	$('.init-project .add-project').first().click();
}
if (typeof(INIT_DATA.new_projects_count) == 'undefined') {
	INIT_DATA.new_projects_count = 0;
}
$('#init .init-footer.calendar').html('<div class="init-footer-content">\
<button class="pull-left an-btn an-btn-danger" id="previous-step">Back</button><form class="hidden" action="<?php echo site_url('meeting/init_import') ?>" enctype="multipart/form-data" id="attachment-form"></form><button class="pull-right an-btn an-btn-success" id="next-step">Next</button>\
</div>')
</script>
<style>
.init-project .action {
	width: 20px;
}

.init-project .action .ion-minus-circled {
	color: #eb547c;
	cursor: pointer;
}

.init-project .action .ion-plus-circled {
	color: #70c1b3;
	cursor: pointer;
}

.init-project select {
	margin-bottom: 0;
}
</style>