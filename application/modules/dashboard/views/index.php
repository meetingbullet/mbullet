<?php
$step_status_labels = [
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
?>

<div class="row">
	<!-- Welcome Professor -->
	<div class="col-md-3 col-xs-12">
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

				<div class="action-panel an-helper-block">
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
				</div>
			</div> <!-- end .welcome-panel -->
		</div>
	</div> <!-- end Welcome Professor -->

	<!-- my projects -->
	<div class="col-md-4 col-xs-12">
		<h1 class="db-h1">
			<span class="an-settings-button">
				<?php echo lang('db_my_projects') ?>&nbsp
				<a href="#" id="create" class="setting"><i class="ion-plus"></i></a>
			</span>
		</h1>
		<div class="project-list">
		<?php if (! empty($projects)) : ?>
			<?php foreach ($projects as $project) : ?>
			<div class="item">
				<?php dump($project) ?>
			</div>
			<?php endforeach ?>
		<?php endif ?>
		</div>
	</div>
	<!-- end my projects -->

	<!-- Calendar -->
	<div class="col-md-5 col-xs-12">
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

<div id="step-decider" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-80" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>

<div id="create-step" class="modal fade" tabindex="-1" role="dialog">
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