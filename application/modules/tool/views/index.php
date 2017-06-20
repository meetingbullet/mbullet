<?php echo form_open() ?>
	<div class="col-xs-12">
	<label>Calendar list:</label>
	<select class="an-form-control" name="calendar_id">
	<?php foreach ($calendar_list as $id => $name) : ?>
		<option value="<?php echo $id ?>" <?php echo set_value('calendar_id') == $id ? 'selected' : '' ?>><?php echo $name ?></option>
	<?php endforeach ?>
	</select>
	</div>
	<div class="col-md-6">
		<label># of previous days:</label>
		<input class="an-form-control" min="0" type="number" placeholder="Number of Previous Days" step="1" value="<?php echo set_value('no_of_prev_days', 90) ?>" name="no_of_prev_days"/>
	</div>
	<div class="col-md-6">
		<label># of next days:</label>
		<input class="an-form-control" min="0" type="number" placeholder="Number of Next Days" step="1" value="<?php echo set_value('no_of_next_days', 90) ?>" name="no_of_next_days"/>
	</div>
	<div class="text-center">
		<button class="rounded an-btn an-btn-primary" type="submit">Summary</button>
	</div>
<?php echo form_close() ?>
<?php if (! empty($event_list)) : ?>
<br/>
<div class="col-xs-12">
	<table class="table">
		<thead>
			<tr>
				<th>#</th>
				<th>Meeting name</th>
				<th>Attendee - Meeting time - Role - Decision</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($event_list as $key => $event) : ?>
			<tr>
				<th scope="row"><?php echo ($key + 1) ?></th>
				<td><?php echo $event->summary ?></td>
				<td>
				<?php if (! empty($event->attendees)) : ?>
					<?php
					$start = strtotime(empty($event->start->dateTime) ? $event->start->date : $event->start->dateTime);
					$end = strtotime(empty($event->end->dateTime) ? $event->end->date : $event->end->dateTime);

					$meeting_time = $end - $start;
					$meeting_time /= 3600;
					?>
					<ul>
					<?php foreach ($event->attendees as $attendee) : ?>
						<li style="<?php echo $attendee->organizer == 1 ? 'color: #025d83;' : '' ?> <?php echo ($user->email == $attendee->email || $attendee->self == 1) ? 'font-style: italic; font-weight: bold;' : ''; ?>"><?php echo "{$attendee->email} - {$meeting_time} hour(s) - " . ($attendee->organizer == 1 ? 'Owner' : 'Participant') . " - {$attendee->responseStatus}" ?></li>
						<?php
						if ($attendee->responseStatus == 'accepted' || $attendee->responseStatus == 'tentative') {
							if (isset($total[$attendee->email])) {
								$total[$attendee->email]['time'] += $meeting_time;
							} else {
								$total[$attendee->email]['email'] = $attendee->email;
								$total[$attendee->email]['time'] = $meeting_time;
								$total[$attendee->email]['self'] = ($user->email == $attendee->email || $attendee->self == 1);
							}
						}
						?>
					<?php endforeach ?>
					</ul>
				<?php endif ?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
		<tfoot>
			<tr>
				<th>Total:</th>
				<td colspan="2">
				<?php if (! empty($total)) : ?>
					<ul>
					<?php foreach ($total as $item) : ?>
						<li style="<?php echo $item['self'] ? 'font-style: italic; font-weight: bold;' : ''; ?>"><?php echo "{$item['email']} - {$item['time']} hour(s)" ?></li>
					<?php endforeach ?>
					</ul>
				<?php endif ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>
<?php endif ?>
