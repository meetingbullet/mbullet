<?php

$breadcrumb[] = [
	'name' => 'Home',
	'path' => DEFAULT_LOGIN_LOCATION
];

if (! is_null($this->uri->segment(1))) {
	switch ($this->uri->segment(1)) {
		case 'dashboard':
			$breadcrumb[0] = [
				'name' => 'Dashboard',
				'path' => null
			];
			break;
		case 'project':
			$breadcrumb[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'action':
			$project = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$breadcrumb[] = [
				'name' => $project,
				'path' => 'project/' . $project
			];

			$breadcrumb[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'meeting':
			$action = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$project = substr($action, 0, strrpos($action, '-'));

			$breadcrumb[] = [
				'name' => $project,
				'path' => 'project/' . $project
			];

			$breadcrumb[] = [
				'name' => $action,
				'path' => 'action/' . $action
			];

			$breadcrumb[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'agenda':
			$step = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$action = substr($step, 0, strrpos($step, '-'));
			$project = substr($action, 0, strrpos($action, '-'));

			$breadcrumb[] = [
				'name' => $project,
				'path' => 'project/' . $project
			];

			$breadcrumb[] = [
				'name' => $action,
				'path' => 'action/' . $action
			];

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