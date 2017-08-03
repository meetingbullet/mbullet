<div>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_create_meeting'))?></h4>
	</div> <!-- end MODAL-HEADER -->

	<div class='container-fluid modal-body'>

		<div class="row">
			<div class="col-md-3 col-sm-12">
				<label for="in" class="pull-right"><?php e(lang('st_project')) ?></label>
			</div>
			<div class="col-md-9 col-sm-12">
				<select name="project" class="an-form-control">
					<option value="">Unspecified Project</option>
					<optgroup label="My projects">
					<?php foreach ($my_projects as $project) : ?>
						<option value="<?php echo $project->cost_code ?>"><?php echo $project->name ?></option>
					<?php endforeach ?>
					<optgroup label="Other projects">
					<?php foreach ($other_projects as $project) : ?>
						<option value="<?php echo $project->cost_code ?>"><?php echo $project->name ?></option>
					<?php endforeach ?>
					</select>
				<input name="in" type="hidden" value="<?php echo (strtotime($this->input->get('end')) - strtotime($this->input->get('start'))) / 60 ?>"/>
				<input name="scheduled_start_time" type="hidden" value="<?php echo $this->input->get('start') ?>" />
			</div>
		</div>

	</div>

	<div class="modal-footer">
		<button type="button" name="save" id="choose-project" class="an-btn an-btn-primary"><?php e(lang('st_choose'))?></button>
		<a href="#" class="an-btn an-btn-danger-transparent" data-dismiss="modal"><?php e(lang('st_cancel'))?></a>
	</div>
</div>