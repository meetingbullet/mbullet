<div class="">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		<h4 class="modal-title"><?php e(lang('st_meeting_summary') . ' - ' . $meeting['name']) ?></h4>
	</div> <!-- end MODAL-HEADER -->

	<div class='container-fluid modal-body'>
		<div class="panel panel-default panel-overview">
			<div class="panel-heading" role="tab">
				<h4 class="panel-title">
					<a href="#overview-body" role="button" data-toggle="collapse" aria-expanded="true"><?php echo lang('st_overview') ?></a>
				</h4>
			</div>
			<div id="overview-body" class="panel-collapse collapse in" role="tabpanel" aria-expanded="true">
				<div class="panel-body">
					<div class="row number-container">
						<div class="col-md-4">
							<i class="ion-android-people"></i>
							<?php echo lang('st_attendees') ?><br>
							<b class="number"><?php echo count($meeting_members) ?></b>
						</div>
						<div class="col-md-4">
							<i class="ion-document-text"></i>
							<?php echo lang('st_agendas') ?><br>
							<b class="number"><?php echo count($meeting_agendas) ?></b>
						</div>
						<div class="col-md-4">
							<i class="ion-edit"></i>
							<?php echo lang('st_homeworks') ?><br>
							<b class="number"><?php echo count($meeting_homeworks) ?></b>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default panel-overview">
			<div class="panel-heading" role="tab">
				<h4 class="panel-title">
					<a href="#general-body" role="button" data-toggle="collapse" aria-expanded="true"><?php echo lang('st_general_info') ?></a>
				</h4>
			</div>
			<div id="general-body" class="panel-collapse collapse in" role="tabpanel" aria-expanded="true">
				<div class="panel-body">
					<div class="row">
						<div class="col-md-4"><label><?php echo lang('st_meeting_key') ?></label></div>
						<div class="col-md-8"><?php echo $meeting['meeting_key'] ?></div>
					</div>
					<div class="row">
						<div class="col-md-4"><label><?php echo lang('st_meeting_name') ?></label></div>
						<div class="col-md-8"><?php echo $meeting['name'] ?></div>
					</div>
					<div class="row">
						<div class="col-md-4"><label><?php echo lang('st_meeting_rating') ?></label></div>
						<div class="col-md-8" style="color: orange;">
						<?php for ($s = 1; $s <= 5; $s++) : ?>
							<?php if (($meeting['average_rate'] - $s) > -1 && ($meeting['average_rate'] - $s) < 0) : ?>
							<i class="ion-ios-star-half"></i>
							<?php elseif (($meeting['average_rate'] - $s) >= 0) : ?>
							<i class="ion-ios-star"></i>
							<?php else : ?>
							<i class="ion-ios-star-outline"></i>
							<?php endif ?>
						<?php endfor ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default panel-overview">
			<div class="panel-heading" role="tab">
				<h4 class="panel-title">
					<a href="#attendees-body" role="button" data-toggle="collapse" aria-expanded="true"><?php echo lang('st_attendees') ?></a>
				</h4>
			</div>
			<div id="attendees-body" class="panel-collapse collapse in" role="tabpanel" aria-expanded="true">
				<div class="an-user-lists">
					<div class="list-title">
						<h6 class="basis-30">#</h6>
						<h6 class="basis-50"><?php echo lang('st_attendee') ?></h6>
						<h6 class="basis-50"><?php echo lang('st_meeting_rating') ?></h6>
					</div>

					<div class="an-lists-body">
					<?php if (! empty($meeting_members)) : ?>
						<?php foreach ($meeting_members as $index => $member) : ?>
						<div class="list-user-single">
							<div class="list-date number basis-30">
								<?php echo $index + 1 ?>
							</div>
							<div class="list-name basis-50">
								<?php echo display_user($member['attendee_email'], $member['attendee_first_name'], $member['attendee_last_name'], $member['attendee_avatar']) ?>
							</div>
							<div class="list-action basis-50" style="color: orange;">
							<?php for ($s = 1; $s <= 5; $s++) : ?>
								<?php if (($member['average_rate'] - $s) > -1 && ($member['average_rate'] - $s) < 0) : ?>
								<i class="ion-ios-star-half"></i>
								<?php elseif (($member['average_rate'] - $s) >= 0) : ?>
								<i class="ion-ios-star"></i>
								<?php else : ?>
								<i class="ion-ios-star-outline"></i>
								<?php endif ?>
							<?php endfor ?>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->
						<?php endforeach ?>
					<?php else : ?>
					<div class="list-user-single">
						<div class="list-text basis-30">
						</div>
						<div class="list-date email approve basis-40"><?php echo lang('st_no_attendees') ?></div>
						<div class="list-text basis-30">
						</div>
					</div>
					<?php endif ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default panel-overview">
			<div class="panel-heading" role="tab">
				<h4 class="panel-title">
					<a href="#agendas-body" role="button" data-toggle="collapse" aria-expanded="true"><?php echo lang('st_agendas') ?></a>
				</h4>
			</div>
			<div id="agendas-body" class="panel-collapse collapse in" role="tabpanel" aria-expanded="true">
				<div class="an-user-lists">
					<div class="list-title">
						<h6 class="basis-30">#</h6>
						<h6 class="basis-50"><?php echo lang('st_agenda') ?></h6>
						<h6 class="basis-50"><?php echo lang('st_meeting_rating') ?></h6>
					</div>

					<div class="an-lists-body">
					<?php if (! empty($meeting_agendas)) : ?>
						<?php foreach ($meeting_agendas as $index => $agenda) : ?>
						<div class="list-user-single">
							<div class="list-date number basis-30">
								<?php echo $index + 1 ?>
							</div>
							<div class="list-name basis-50">
								<?php echo $agenda['name'] ?>
							</div>
							<div class="list-action basis-50" style="color: orange;">
							<?php for ($s = 1; $s <= 5; $s++) : ?>
								<?php if (($agenda['average_rate'] - $s) > -1 && ($agenda['average_rate'] - $s) < 0) : ?>
								<i class="ion-ios-star-half"></i>
								<?php elseif (($agenda['average_rate'] - $s) >= 0) : ?>
								<i class="ion-ios-star"></i>
								<?php else : ?>
								<i class="ion-ios-star-outline"></i>
								<?php endif ?>
							<?php endfor ?>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->
						<?php endforeach ?>
					<?php else : ?>
					<div class="list-user-single">
						<div class="list-text basis-30">
						</div>
						<div class="list-date email approve basis-40"><?php echo lang('st_no_agendas') ?></div>
						<div class="list-text basis-30">
						</div>
					</div>
					<?php endif ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel panel-default panel-overview">
			<div class="panel-heading" role="tab">
				<h4 class="panel-title">
					<a href="#hw-body" role="button" data-toggle="collapse" aria-expanded="true"><?php echo lang('st_homeworks') ?></a>
				</h4>
			</div>
			<div id="hw-body" class="panel-collapse collapse in" role="tabpanel" aria-expanded="true">
				<div class="an-user-lists">
					<div class="list-title">
						<h6 class="basis-30">#</h6>
						<h6 class="basis-50"><?php echo lang('st_homework') ?></h6>
						<h6 class="basis-50"><?php echo lang('st_meeting_rating') ?></h6>
					</div>

					<div class="an-lists-body">
					<?php if (! empty($meeting_homeworks)) : ?>
						<?php foreach ($meeting_homeworks as $index => $hw) : ?>
						<div class="list-user-single">
							<div class="list-date number basis-30">
								<?php echo $index + 1 ?>
							</div>
							<div class="list-name basis-50">
								<?php echo $hw['name'] ?>
							</div>
							<div class="list-action basis-50" style="color: orange;">
							<?php for ($s = 1; $s <= 5; $s++) : ?>
								<?php if (($hw['average_rate'] - $s) > -1 && ($hw['average_rate'] - $s) < 0) : ?>
								<i class="ion-ios-star-half"></i>
								<?php elseif (($hw['average_rate'] - $s) >= 0) : ?>
								<i class="ion-ios-star"></i>
								<?php else : ?>
								<i class="ion-ios-star-outline"></i>
								<?php endif ?>
							<?php endfor ?>
							</div>
						</div> <!-- end .USER-LIST-SINGLE -->
						<?php endforeach ?>
					<?php else : ?>
					<div class="list-user-single">
						<div class="list-text basis-30">
						</div>
						<div class="list-date email approve basis-40"><?php echo lang('st_no_homeworks') ?></div>
						<div class="list-text basis-30">
						</div>
					</div>
					<?php endif ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal-footer">
		<a href="#" class="an-btn an-btn-danger-transparent" data-dismiss="modal"><?php e(lang('st_cancel'))?></a>
	</div>
</div>