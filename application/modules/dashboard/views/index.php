<button class="an-btn an-btn-primary" id="create" style="margin: 30px 0">Create Project</button>


<div class="an-single-component with-shadow">
	<div class="an-component-header">
		<h6>Projects</h6>
		<div class="component-header-right">
		</div>
	</div>
	<div class="an-component-body">
		<div class="an-user-lists messages">
			<div class="list-title">
				<h6 class="basis-10">
					<span class="an-custom-checkbox">
						<input type="checkbox" id="check-1">
						<label for="check-1"></label>
					</span>
					ID
				</h6>
				<h6 class="basis-30">Project Name</h6>
				<h6 class="basis-20">Cost Code</h6>
				<h6 class="basis-30">Owner</h6>
				<h6 class="basis-10">Status</h6>
				<h6 class="basis-20">Created on</h6>
			</div>

			<div class="an-lists-body an-customScrollbar ps-container ps-theme-default ps-active-y">
				<?php foreach($projects as $project): ?>
				<div class="list-user-single">
					<div class="list-name basis-10">
						<span class="an-custom-checkbox">
							<input type="checkbox" id="check-2">
							<label for="check-2"></label>
						</span>

						<?php e($project->project_id)?>
					</div>
					<div class="list-date basis-30">
						<?php echo anchor(site_url() . 'projects/' . $project->cost_code, $project->name); ?>
					</div>
					<div class="list-date basis-20">
						<?php e($project->cost_code)?>
					</div>
					<div class="list-text basis-30">
						<?php e($project->first_name .' '. $project->last_name)?>
					</div>
					<div class="list-state basis-10">
						<?php e($project->status)?>
					</div>
					<div class="list-action basis-20">
						<?php e($project->created_on)?>
					</div>
				</div> <!-- end .USER-LIST-SINGLE -->
				<?php endforeach; ?>
			</div>
		</div>
	</div> <!-- end .AN-COMPONENT-BODY -->
</div>

<!-- Modal -->
<div class="modal fade" id="bigModal" tabindex="-1" role="dialog" aria-labelledby="bigModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<script>
	var CREATE_PROJECT_URL = '<?php echo site_url('projects/create')?>';
</script>