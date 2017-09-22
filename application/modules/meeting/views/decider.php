<?php
$is_owner = $meeting->owner_id == $current_user->user_id;
$scheduled_start_time = null;

if ($meeting->scheduled_start_time) {
	// Fix add StrToTime with Float number
	if ( (int) $meeting->in !== $meeting->in ) {
		switch ($meeting->in_type) {
			case 'weeks':
				$meeting->in *= 7;
			case 'days':
				$meeting->in *= 24;
			case 'hours':
				$meeting->in *= 60;
			case 'minutes':
				$meeting->in *= 60;
		}
	}

	$scheduled_start_time = strtotime($meeting->scheduled_start_time);
	$scheduled_end_time = strtotime('+' . $meeting->in . ' seconds', $scheduled_start_time);
	$meeting->in = round( $meeting->in / 60, 2);
	$meeting->in_type = 'minutes';

	$scheduled_start_time = date('Y-m-d H:i:s', $scheduled_start_time);
	$scheduled_end_time = date('Y-m-d H:i:s', $scheduled_end_time);
}

$scheduled_time = $scheduled_start_time ? $scheduled_start_time . ' - ' . $scheduled_end_time : null;

$agenda_status_labels = [
	'open' => 'label label-default label-bordered',
	'inprogress' => 'label label-warning label-bordered',
	'resolved' => 'label label-success label-bordered',
	'jumped' => 'label label-info label-bordered',
	'skipped' => 'label label-success label-bordered',
	'parking_lot' => 'label label-info label-bordered',
];

$confirmation_status = [
	'closed', 'skipped', 'resolved', 'open_parking_lot', 'closed_parking_lot'
];

?>
<div class="meeting-decider" data-is-owner="<?php echo $is_owner ? 1 : 0 ?>">
	<?php if (IS_AJAX): ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_meeting_decider'))?></h4>
	</div> <!-- end MODAL-HEADER -->
	<?php endif; ?>

		<div class="an-body-topbar">
			<div class="an-page-title">
				<h2><?php e($meeting->name)?></h2>
			</div>
			<div class="pull-right">
			</div>
		</div> <!-- end AN-BODY-TOPBAR -->

		<div class="decider-meeting-container">
			<div class="row">
				<div id="meeting-info" class="col-md-7">
					<div class="row">
						<div class="col-md-12">
							<div class="an-single-component with-shadow">
								<div class="an-component-header">
									<h6><?php e(lang('st_goal'))?></h6>
								</div>
								<div class="an-component-body an-helper-block">
									<div class="meeting-goal-container readmore-container">
										<div class="goal">
											<?php echo $meeting->goal?>
										</div>
									</div>
								</div> <!-- end .AN-COMPONENT-BODY -->
							</div>
						</div>

						<div class="col-md-12">
							<div class="an-single-component with-shadow">
							<div class="an-component-header">
									<h6><?php e(lang('st_notes'))?></h6>
								</div>
								<div class="an-component-body an-helper-block">
									<textarea id="note" name="note" rows="6" class="an-form-control note" style="border: none;" placeholder="<?php e(lang('st_write_a_note_here')) ?>"></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="an-single-component with-shadow">
								<div class="an-component-body an-helper-block">
									<table class="table table-striped table-meeting-time">
										<thead>
											<tr>
												<th></th>
												<th class="text-center"><?php e(lang('st_scheduled'))?></th>
												<th class="text-center"><?php e(lang('st_actual'))?></th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td><strong><?php e(lang('st_start_time')) ?></strong></td>
												<td class="text-center"><?php echo display_time($scheduled_start_time) ?></td>
												<td class="text-center"><?php echo display_time($meeting->actual_start_time) ?></td>
											</tr>
											<tr>
												<td><strong><?php e(lang('st_end_time')) ?></strong></td>
												<td class="text-center"><?php echo display_time($scheduled_end_time) ?></td>
												<td class="text-center"><?php echo display_time($meeting->actual_end_time) ?></td>
											</tr>
											<tr>
												<td><strong><?php e(lang('st_elapsed_time')) ?></strong></td>
												<td class="text-center"><?php echo timespan(strtotime($meeting->scheduled_start_time), strtotime($scheduled_end_time) ) ?></td>
												<td class="text-center"><?php echo timespan(strtotime($meeting->actual_start_time), strtotime($meeting->actual_end_time)) ?></td>
											</tr>
										</tbody>
									</table>
								</div> <!-- end .AN-COMPONENT-BODY -->
							</div>
						</div>
					</div> <!-- end .row -->
				</div>

				<div class="col-md-5">
					<div class="an-single-component fixed-height with-shadow">
						<div class="an-component-header">
							<h6><?php e(lang('st_attendees'))?></h6>
							<div style="display:none" class="rating">
								<input type="radio" id="star5" value="5" /><label class = "full" for="star5" title="5 stars"></label>
								<input type="radio" id="star4" value="4" /><label class = "full" for="star4" title="4 stars"></label>
								<input type="radio" id="star3" value="3" /><label class = "full" for="star3" title="3 stars"></label>
								<input type="radio" id="star2" value="2" /><label class = "full" for="star2" title="2 stars"></label>
								<input type="radio" id="star1" value="1" /><label class = "full" for="star1" title="1 star"></label>
							</div>
						</div>
						<div class="an-component-body an-helper-block">
							<form id="rating-form">
								<ul class="list-unstyled list-member">
									<?php foreach ($meeting->members as $user) { ?>
									<li>
										<?php echo display_user($user['email'], $user['first_name'], $user['last_name'], $user['avatar']); ?>
										<span class="badge badge-<?php e($user['cost_of_time'])?> badge-bordered pull-right" style="margin-top: 4px; margin-left: 5px;"><?php e($user['cost_of_time_name'])?></span>
										<div class="rating pull-right">
											<input type="radio" id="star5" name="attendee_rate[<?php echo $user['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $user['user_id'] . ']', 5) ?> value="5" /><label class = "full" for="star5" title="5 stars"></label>
											<input type="radio" id="star4" name="attendee_rate[<?php echo $user['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $user['user_id'] . ']', 4) ?> value="4" /><label class = "full" for="star4" title="4 stars"></label>
											<input type="radio" id="star3" name="attendee_rate[<?php echo $user['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $user['user_id'] . ']', 3) ?> value="3" /><label class = "full" for="star3" title="3 stars"></label>
											<input type="radio" id="star2" name="attendee_rate[<?php echo $user['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $user['user_id'] . ']', 2) ?> value="2" /><label class = "full" for="star2" title="2 stars"></label>
											<input type="radio" id="star1" name="attendee_rate[<?php echo $user['user_id'] ?>]" <?php echo set_radio('attendee_rate[' . $user['user_id'] . ']', 1) ?> value="1" /><label class = "full" for="star1" title="1 star"></label>
										</div>
									</li>
									<?php } ?>
								</ul>
							</form>
						</div> <!-- end .AN-COMPONENT-BODY -->
					</div>
				</div>

				<div class="col-md-4 hidden">
					<div id="comment">
						<div class="an-single-component with-shadow">
							<div class="an-component-header">
								<h6><?php e(lang('mt_comments'))?></h6>

								<span class="badge badge-bordered badge-warning badge-comment" style="display: none">
									<span class="number"></span> <span><?php echo lang('mt_new_message') ?></span>
								</span>
							</div>
							<div class="an-component-body">
								<div class="an-user-lists chat-container chat-page">
									<div id="comment-body" class="an-lists-body">
										<?php foreach ($comments as $comment): ?>
										<div data-id="<?php echo $comment->meeting_comment_id ?>" class="list-user-single">
											<div class="list-name">
												<span class="avatar" 
													style="background-image: url('<?php echo avatar_url($comment->avatar, $comment->email) ?>'); width: 30px; height: 30px;">
												</span>
												<a href="#" target="_blank">
													<span class="name"><?php echo $comment->full_name ?></span>
													<?php if ($comment->is_owner == '1'): ?>
													<span class="badge badge-bordered badge-owner"><?php echo lang('mt_owner') ?></span>
													<?php endif; ?>
													<span class="an-time">
														<i class="icon-clock"></i>
														<span class="time" data-created-on="<?php echo $comment->created_on ?>"></span>
													</span>
												</a>
											</div>
											<p class="comment"><?php e($comment->comment) ?></p>
										</div> <!-- end .USER-LIST-SINGLE -->
										<?php endforeach; ?>
									</div> <!-- end .AN-LISTS-BODY -->
									<div class="an-chat-form">
										<form class="an-form" action="#">
										<div class="an-search-field topbar">
											<input id="send-comment" class="an-form-control" type="text" placeholder="<?php echo lang('mt_type_a_comment') ?>"
													autocomplete="off"
													data-i-am-owner="<?php echo (int) $is_owner ?>"
													data-my-full-name="<?php echo $current_user->first_name .' '. $current_user->last_name ?>"
													data-my-avatar-url="<?php echo avatar_url($current_user->avatar, $current_user->email) ?>"
											>
											<button class="an-btn an-btn-icon btn-send-comment">
												<i class="ion-paper-airplane"></i>
											</button>
										</div>
										</form>
									</div>
								</div>
							</div> <!-- end .AN-COMPONENT-BODY -->
						</div>
					</div> <!-- end #comment -->
				</div>
			</div>
		</div> <!-- / .meeting-decider-container -->
	<?php echo form_open(site_url('meeting/update_decider/' . $meeting->meeting_key), ['class' => 'form-inline form-meeting-decider an-helper-block']) ?>
		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_agendas'))?></h6>
			</div>
			<div class="an-component-body an-helper-block">
				<table class="table table-striped table-meeting-time">
					<thead>
						<tr>
							<th><?php e(lang('st_name'))?></th>
							<th><?php e(lang('st_started_on'))?></th>
							<th><?php e(lang('st_duration'))?></th>
							<th><?php e(lang('st_meeting_status'))?></th>
							<th><?php e(lang('st_confirmation_status'))?></th>
						</tr>
					</thead>
					<tbody>
						<?php if($agendas): foreach ($agendas as $agenda) : ?>
						<tr id='agenda-<?php e($agenda->agenda_id)?>' data-agenda-id='<?php e($agenda->agenda_id)?>' data-agenda-status='<?php e($agenda->status)?>'>
							<td><?php echo anchor(site_url('agenda/' . $agenda->agenda_key), $agenda->name, ['target' => '_blank'])?></td>
							<td><?php echo display_time($agenda->started_on) ?></td>
							<td><?php echo $agenda->started_on ? timespan(strtotime($agenda->started_on), strtotime($agenda->finished_on)) : '0' ?></td>
							<td>
								<span class="label label-bordered label-<?php e($agenda->status)?>">
									<?php e(lang('st_' . $agenda->status))?>
								</span>
							</td>
							<td>
								<select name="agendas[<?php e($agenda->agenda_key) ?>]" class="confirmation-status an-form-control">
									<option disabled selected value><?php e(lang('st_select_an_option')) ?></option>
									<?php foreach ($confirmation_status as $status) {
										echo "<option value='{$status}' ". ($agenda->confirm_status == $status ? ' selected' : '') .">". lang('st_' . $status) ."</option>";
									} ?>
								</select>
							</td>
						</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>

		<div class="an-single-component with-shadow">
			<div class="an-component-header">
				<h6><?php e(lang('st_homeworks'))?></h6>
			</div>
			<div class="an-component-body an-helper-block">
				<table class="table table-striped table-agenda">
					<thead>
						<tr>
							<th><?php e(lang('hw_name'))?></th>
							<th><?php e(lang('hw_description'))?></th>
							<th class="text-center"><?php echo lang('hw_time_spent') ?></th>
							<th><?php echo lang('hw_attachment') ?></th>
							<th class="text-center"><?php e(lang('st_status'))?></th>
						</tr>
					</thead>
					<tbody>
						<?php if(is_array($homeworks)): foreach ($homeworks as $homework) : ?>
						<tr>
							<td><?php echo $homework->name ?></td>
							<td><?php echo $homework->description ?></td>
							<td class='text-center'><?php echo $homework->time_spent ?></td>
							<td>
								<?php if ($homework->attachments): ?>
								<div class="attachment">
									<?php foreach ($homework->attachments as $att): ?>
									<a href="<?php echo $att->url ?>" target="_blank">
										<span class="icon">
											<?php if ($att->favicon): ?>
											<img src="<?php echo $att->favicon ?>" data-toggle="tooltip" alt="[A]" title="<?php echo $att->title ? $att->title : $att->url ?>">
											<?php else: ?>
											<i class="icon-file" data-toggle="tooltip" title="<?php echo $att->title ? $att->title : $att->url ?>"></i>
											<?php endif; ?>
										</span>
									</a>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>
							</td>
							<td class="text-center agenda-status">
								<?php if (! empty($homework->status)) : ?>
								<span class="label label-bordered label-<?php e($homework->status)?>"><?php e(lang('st_' . $homework->status))?></span>
								<?php endif ?>
							</td>
						</tr>
						<?php endforeach; else : ?>
						<tr>
							<td colspan="5" class="text-center"><?php echo lang('st_no_homeworks') ?></td>
						</tr>
						<?php endif ?>
					</tbody>
				</table>
			</div> <!-- end .AN-COMPONENT-BODY -->
		</div>

		<div class="modal-footer">
			<button class="an-btn an-btn-primary btn-close-out-meeting pull-right"><?php e(lang('st_close_out_meeting')) ?></button>
		</div>

	<?php echo form_close() ?>
</div>

<style>
.an-body-topbar,
.decider-meeting-container {
	padding-left: 15px;
	padding-right: 15px;
}
</style>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('decider_js', [
		'project_key' => $project_key,
		'meeting_key' => $meeting->meeting_key,
		'meeting_id' => $meeting->meeting_id,
		'is_owner' => $is_owner,
	], true) . '</script>';
}
?>

<?php if ( ! IS_AJAX): ?>
<!-- Modal -->
<div id="resolve-agenda" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		</div>
	</div>
</div>
<?php endif; ?>

<script id="single-comment" type="ajax/vithd">
	<div data-id="{{:id}}" class="list-user-single{{if mark_as_read==false}} unread{{/if}}" style="display:none">
		<div class="list-name">
			<span class="avatar" 
				style="background-image: url('{{:avatar_url}}'); width: 30px; height: 30px;">
			</span>
			<a href="#" target="_blank">
				<span class="name">{{:full_name}}</span>
				{{if is_owner=="1"}}<span class="badge badge-bordered badge-owner"><?php echo lang('mt_owner') ?></span>{{/if}}
				<span class="an-time">
					<i class="icon-clock"></i>
					<span class="time" data-created-on="{{:created_on}}"><?php echo lang('mt_a_few_second_ago') ?></span>
				</span>
			</a>
		</div>
		<p class="comment">{{:comment}}</p>
	</div> <!-- end .USER-LIST-SINGLE -->
</script>