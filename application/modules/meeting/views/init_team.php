<div class="init-team">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Team member</th>
				<th>Existing in the MB</th>
				<th>Will be invited</th>
				<th>As Owner</th>
				<th>As Guest</th>
				<th>Projects</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($users as $email => $user) : ?>
			<tr>
				<td><?php echo empty($user['name']) ? $email : $user['name'] ?></td>
				<td>
				<?php if (! empty($user['existed'])) : ?>
					<i class="ion-checkmark"></i>
				<?php endif ?>
				</td>
				<td>
				<?php if (empty($user['existed'])) : ?>
					<i class="ion-checkmark"></i>
				<?php endif ?>
				</td>
				<td>
				<?php if (! empty($user['as_owner'])) : ?>
					<i class="ion-checkmark"></i>
				<?php endif ?>
				</td>
				<td>
				<?php if (! empty($user['as_guest'])) : ?>
					<i class="ion-checkmark"></i>
				<?php endif ?>
				</td>
				<td><?php echo count($user['projects']) ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</div>