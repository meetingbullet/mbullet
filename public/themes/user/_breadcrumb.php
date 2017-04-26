<?php

$breadcrumb[] = [
	'name' => 'Home',
	'path' => DEFAULT_LOGIN_LOCATION
];

if (! is_null($this->uri->segment(1))) {
	switch ($this->uri->segment(1)) {
		case 'dashboard':
			$breadcrumb[0] = [
				'name' => 'Home',
				'path' => null
			];
			break;
		case 'projects':
			$breadcrumb[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'action':
			$project = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$breadcrumb[] = [
				'name' => $project,
				'path' => 'projects/' . $project
			];

			$breadcrumb[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'step':
			$project = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$breadcrumb[] = [
				'name' => $project,
				'path' => 'projects/' . $project
			];

			$action = substr($project, 0, strrpos($project, '-'));
			$breadcrumb[] = [
				'name' => $action,
				'path' => 'action/' . $action
			];

			$breadcrumb[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'task':
			$project = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$breadcrumb[] = [
				'name' => $project,
				'path' => 'projects/' . $project
			];

			$action = substr($project, 0, strrpos($project, '-'));
			$breadcrumb[] = [
				'name' => $action,
				'path' => 'action/' . $action
			];

			$step = substr($action, 0, strrpos($action, '-'));
			$breadcrumb[] = [
				'name' => $step,
				'path' => 'step/' . $step
			];

			$breadcrumb[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
	}
}
?>
				<div class="an-breadcrumb wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
					<ol class="breadcrumb">
<?php 
foreach ($breadcrumb as $item) {
	if (is_null($item['path'])) {
?>
						<li class="active"><?php echo $item['name']; ?></li>
<?php
	} else {
?>
						<li><a href="<?php echo site_url($item['path']); ?>"><?php echo $item['name']; ?></a></li>
<?php
	}
}
?>
					</ol>
				</div>