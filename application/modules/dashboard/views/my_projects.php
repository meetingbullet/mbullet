<?php if (! empty($projects)) : ?>
	<?php foreach ($projects as $project) : ?>
	<div class="item">
		<div class="general-info">
			<h3><?php echo "{$project->name} [{$project->cost_code}]" ?></h3>
			<div class="project-info">
				<div class="col-xs-4">
					<label><?php echo lang('db_project_pts') ?></label>
					<p><?php echo (empty($project->point_used) ? 0 : $project->point_used) . "/" . (empty($project->project_no_of_point) ? 0 : $project->project_no_of_point) ?></p>
				</div>
				<div class="col-xs-4">
					<label><?php echo lang('db_meetings') ?></label>
					<p><?php echo (empty($project->no_of_unfinished_step) ? '0' : $project->no_of_unfinished_step) . "/" . (empty($project->no_of_step) ? '0' : $project->no_of_step) ?></p>
				</div>
				<div class="col-xs-2">
					<i class="ion-android-star" style="color: orange; font-size: 25px;"></i>
					<p><?php echo (empty($project->total_rate) ? '0' : $project->total_rate) . "/" . (empty($project->max_rate) ? '0' : $project->max_rate) ?></p>
				</div>
				<div class="col-xs-2">
					<i class="ion-ios-people" style="font-size: 25px;"></i>
					<p><?php echo $project->member_number ?></p>
				</div>
			</div>
		</div>
		<div class="owners">
			<?php foreach ($project->step_owners as $owner) : ?>
			<div class="item">
				<div class="owner-info">
					<?php echo display_user($owner['info']['email'], $owner['info']['first_name'], $owner['info']['last_name'], $owner['info']['avatar']) . ' <span style="text-transform: uppercase; color: #025d83; vertical-align: middle;">' . lang('db_has_the_ball') . '!</span>' ?>
				</div>
				<div class="steps">
					<?php foreach($owner['items'] as $step) : ?>
					<div class="item">
						<a href="<?php echo site_url('step/' . $step->step_key) ?>"><?php echo "<span class='msg-tag label label-bordered label-inactive'>{$step->step_key}</span> {$step->name}" ?></a>
					</div>
					<?php endforeach ?>
				</div>
			</div>
			<?php endforeach ?>
		</div>
	</div>
	<?php endforeach ?>
<?php endif ?>