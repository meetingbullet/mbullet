<?php
$meeting_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'finished' => 'label label-success label-bordered',
	'ready' => 'label label-info label-bordered',
];

$project_status_labels = [
	'open' => 'label label-default label-bordered',
	'inactive' => 'label label-warning label-bordered',
	'archive' => 'label label-success label-bordered',
];
$confirmation_status = [
	'closed', 'skipped', 'resolved', 'open_parking_lot', 'closed_parking_lot'
];
?>
<div style="display: none;" class="todo-rating">
	<input type="radio" id="star5" value="5" /><label class = "full" for="star5" title="5 stars"></label>
	<input type="radio" id="star4" value="4" /><label class = "full" for="star4" title="4 stars"></label>
	<input type="radio" id="star3" value="3" /><label class = "full" for="star3" title="3 stars"></label>
	<input type="radio" id="star2" value="2" /><label class = "full" for="star2" title="2 stars"></label>
	<input type="radio" id="star1" value="1" /><label class = "full" for="star1" title="1 star"></label>
</div>
<div class="row">
	<!-- Welcome Professor -->
	<div class="col-md-4 col-xs-12">
		<div class="welcome-panel">
			<div class="user-info">
				<div class="avatar" style="background-image: url('<?php echo avatar_url($user->avatar, $user->email, 128) ?>')"></div>
				<div class="text-center">
					<h3><?php echo sprintf(lang('db_welcome_x'), $user->first_name)?></h3>

					<div class="rate">
						<span class="average-star">
							<?php 
								$counting_stars = $user->avarage_rate;
								$all_star = 5;
								while ($all_star --) {

									if ($counting_stars-- > 0) {
										echo '<i class="ion-android-star"></i>';
									} else {
										echo '<i class="ion-android-star-outline"></i>';
									}
								}
							?>
						</span>

						<?php if ($user->meeting_count !== null): ?>
						<span class="meeting-count">
							<?php e(
								sprintf($user->meeting_count > 1 
										? lang('db_x_meetings') 
										: lang('db_x_meeting')
								, $user->meeting_count)
							) ?>
						</span>
						<?php endif; ?>
					</div>
					<div class="total-points"><?php e(sprintf(lang('db_total_points_x'), round($user->total_point_used, 2) )) ?></div>
				</div> <!-- end .user-info -->

				<!--div class="action-panel an-helper-block">
					<span class="text-muted"><?php e(lang('db_what_do_you_want_to_do'))?></span>
					<hr>
					<div class="btn-group">
						<button type="button" class="an-btn an-btn-primary block-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="ion-ios-plus-outline"></i>
							<?php e(lang('db_create_work')) ?> <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="#">Action</a></li>
							<li><a href="#">Another action</a></li>
							<li><a href="#">Something else here</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="#">Separated link</a></li>
						</ul>
					</div>

					<button class="an-btn an-btn-primary block-icon"><i class="ion-ios-play-outline"></i><?php e(lang('db_do_work')) ?></button><br>
					<button class="an-btn an-btn-default block-icon"><i class="ion-ios-speedometer-outline"></i><?php e(lang('db_review_work')) ?></button><br>
				</div-->
				<div class="my-todo">
					<h1 class="db-h1">
						<span class="an-settings-button">
							<?php echo lang('db_my_todo') ?>&nbsp
							<!--a href="#" class="setting"><i class="ion-plus"></i></a-->
						</span>
					</h1>
					<div class="todo-list">
					<?php if (! empty($my_todo)) : ?>
						<?php foreach ($my_todo as $todo) : if (! ($todo->todo_type == 'evaluate' && $todo->evaluate_mode == 'user' && $todo->user_id == $current_user->user_id)) : ?>
						<div class="item">
							<?php if ($todo->todo_type == 'homework') : ?>
							<div class="todo-left">
								<span class="msg-tag label label-bordered label-inprogress">
									<?php echo lang('db_homework') ?>
								</span>&nbsp;
								<?php echo ucfirst($todo->name) . ": " . ucfirst($todo->description) ?>
							</div>
							<div class="todo-right">
								<div class="detail">
									<a href="<?php echo site_url('meeting/') . $todo->meeting_key ?>" target="_blank">
										<i class="ion-document"></i>
									</a>
								</div>
								<a href="#" class="setting action an-btn-danger submit undone"><i class="ion-close"></i></a>
								<a href="#" class="setting action an-btn-info submit done"><i class="ion-checkmark"></i></a>
							</div>
							<?php elseif ($todo->todo_type == 'evaluate') : ?>
							<div class="todo-left">
								<span class="msg-tag label label-bordered label-skipped">
									<?php echo lang('db_evaluate') ?>
								</span>&nbsp;
								<?php if ($todo->evaluate_mode == 'agenda') : ?>
									<?php echo "[" . $todo->meeting_key . "] " . ucfirst($todo->meeting_name) . ": " . "[" . $todo->agenda_key . "] " . ucfirst($todo->agenda_name) . " - " . word_limiter(ucfirst($todo->agenda_description), 20, '...') ?>
								<?php else : ?>
									<?php echo "[" . $todo->meeting_key . "] " . ucfirst($todo->meeting_name) . ": " . display_user($todo->email, $todo->first_name, $todo->last_name, $todo->avatar); ?>
								<?php endif ?>
							</div>
							<div class="todo-right">
								<a href="#" class="setting action an-btn-info submit"><i class="ion-checkmark"></i></a>
								<div class="todo-rating-wraper">
									<div class="todo-rating">
										<input type="radio" id="star5" value="5" /><label class = "full" for="star5" title="5 stars"></label>
										<input type="radio" id="star4" value="4" /><label class = "full" for="star4" title="4 stars"></label>
										<input type="radio" id="star3" value="3" /><label class = "full" for="star3" title="3 stars"></label>
										<input type="radio" id="star2" value="2" /><label class = "full" for="star2" title="2 stars"></label>
										<input type="radio" id="star1" value="1" /><label class = "full" for="star1" title="1 star"></label>
									</div>
								</div>
							</div>
							<?php else : ?>
							<div class="todo-left">
								<span class="msg-tag label label-bordered label-ready">
									<?php echo lang('db_decide') ?>
								</span>&nbsp;
								<?php echo "[" . $todo->meeting_key . "] " . ucfirst($todo->meeting_name) . ": " . "[" . $todo->agenda_key . "] " . ucfirst($todo->agenda_name) . " - " . word_limiter(ucfirst($todo->agenda_description), 20, '...') ?>
							</div>
							<div class="todo-right">
								<select name="" class="an-form-control">
									<option disabled selected value><?php e(lang('st_select_an_option')) ?></option>
									<?php foreach ($confirmation_status as $status) : ?>
										<option value="$status"><?php echo lang('db_' . $status) ?></option>
									<?php endforeach ?>
								</select>
								<a href="#" class="setting action an-btn-info submit"><i class="ion-checkmark"></i></a>
							</div>
							<?php endif ?>
						</div>
						<?php endif; endforeach; ?>
					<?php endif ?>
					</div>
				</div>
			</div> <!-- end .welcome-panel -->
		</div>
	</div> <!-- end Welcome Professor -->

	<!-- my projects -->
	<div class="col-md-4 col-xs-12 my-projects" data-my-projects-url="<?php echo site_url('dashboard/my_projects') ?>">
		<h1 class="db-h1">
			<span class="an-settings-button">
				<?php echo lang('db_my_projects') ?>&nbsp
				<a href="#" id="create" class="setting"><i class="ion-plus"></i></a>
			</span>
		</h1>
		<div class="project-list" style="">
		<?php if (! empty($projects)) : ?>
			<?php foreach ($projects as $project) : ?>
			<div class="item">
				<div class="general-info">
					<h3><a href="<?php echo site_url('project/' . $project->cost_code) ?>"><?php echo "{$project->name} [{$project->cost_code}]" ?></a></h3>
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
								<a href="<?php echo site_url('meeting/' . $step->meeting_key) ?>"><?php echo "<span class='msg-tag label label-bordered label-inactive'>{$step->meeting_key}</span> {$step->name}" ?></a>
							</div>
							<?php endforeach ?>
						</div>
					</div>
					<?php endforeach ?>
				</div>
			</div>
			<?php endforeach ?>
		<?php endif ?>
		</div>
	</div>
	<!-- end my projects -->

	<!-- Calendar -->
	<div class="col-md-4 col-xs-12">
		<h1 class="db-h1">
			<span class="an-settings-button">
				<?php echo lang('db_meetings') ?>&nbsp
				<a href="#" id="create_meeting" class="setting hidden"><i class="ion-plus"></i></a>
			</span>
		</h1>
		
		<div class="calendar-container">
			<div id="meeting-calendar"></div>
		</div>
	</div>
	<!-- end Calendar -->
</div>



<!-- Modal -->
<div class="modal fade" id="bigModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal fade" id="inviteModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal modal-monitor fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="resolve-agenda" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="meeting-decider" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="create-meeting" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal modal-monitor-evaluator fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div class="modal waiting-modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<?php echo '<p class="text-center">' . lang('st_waiting_evaluator') . '</p>' ?>
		</div>
	</div>
</div>

<script>
	var CREATE_PROJECT_URL = '<?php echo site_url('project/create')?>';
	var INVITE_USER_URL = '<?php echo site_url('admin/team/invite')?>';
</script>

<script id="project-row" type="text">
	<div class="list-user-single">
		<div class="list-date basis-10">{{:project_id}}</div>
		<div class="list-name basis-30"><a href="<?php echo site_url('project')?>/{{:cost_code}}">{{:name}}</a></div>
		<div class="list-date basis-20">{{:cost_code}}</div>
		<div class="list-text basis-30">{{:display_user}}</div>
		<div class="list-state basis-10">
			<span class="msg-tag label label-bordered label-{{:status}}">{{:lang_status}}</span>
		</div>
		<div class="list-action basis-20">{{:created_on}}</div>
	</div>
</script>