<div class="init-team">
	<table class="table table-striped">
		<thead>
			<tr>
				<th></th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>Meetings</th>
				<td><?php echo $summary['meetings_count'] ?></td>
			</tr>
			<tr>
				<th>Existing Users</th>
				<td><?php echo $summary['existed_users_count'] ?></td>
			</tr>
			<tr>
				<th>Invited Users</th>
				<td><?php echo $summary['new_users_count'] ?></td>
			</tr>
			<tr>
				<th>Used Existing Projects</th>
				<td><?php echo $summary['existed_projects_count'] ?></td>
			</tr>
			<tr>
				<th>New Projects</th>
				<td><?php echo $summary['new_projects_count'] ?></td>
			</tr>
			<tr>
				<th>Total meeting time</th>
				<td><?php echo $summary['total_time'] ?></td>
			</tr>
		</tbody>
	</table>
</div>