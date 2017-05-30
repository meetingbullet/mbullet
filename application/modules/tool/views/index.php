<?php echo form_open() ?>
	<label>Calendar list:</label>
	<select class="an-form-control" name="calendar_id">
	<?php foreach ($calendar_list as $id => $name) : ?>
		<option value="<?php echo $id ?>" <?php echo set_value('calendar_id') == $id ? 'selected' : '' ?>><?php echo $name ?></option>
	<?php endforeach ?>
	</select>
	<label># of days:</label>
	<input class="an-form-control" min="0" type="number" placeholder="Number of Previous Days" step="1" value="<?php echo set_value('no_of_days', 90) ?>" name="no_of_days"/>
	<div class="text-center">
		<button class="rounded an-btn an-btn-primary" type="submit">Summary</button>
	</div>
<?php echo form_close() ?>
<?php if (! empty($event_list)) : ?>
<br/>
<div>
	<table class="table">
		<thead>
			<tr>
			<th>#</th>
			<th>Meeting name</th>
			<th>Attendee - Meeting time</th>
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
				<?php foreach ($event->attendees as $index => $attendee) : ?>
					<li style="<?php echo $user->email == $attendee->email ? 'font-style: italic; font-weight: bold;' : ''; ?>"><?php echo "{$attendee->email} - {$meeting_time} hour(s)" ?></li>
					<?php
					if (isset($total[$index])) {
						$total[$index]['time'] += $meeting_time;
					} else {
						$total[$index]['email'] = $attendee->email;
						$total[$index]['time'] = $meeting_time;
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
						<li style="<?php echo $user->email == $item['email'] ? 'font-style: italic; font-weight: bold;' : ''; ?>"><?php echo "{$item['email']} - {$item['time']} hour(s)" ?></li>
					<?php endforeach ?>
					</ul>
				<?php endif ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>
<?php endif ?>
