<?php
$breadcrumb_arr[] = [
	'name' => 'Home',
	'path' => DEFAULT_LOGIN_LOCATION
];

if (! is_null($this->uri->segment(1))) {
	switch ($this->uri->segment(1)) {
		case 'dashboard':
			$breadcrumb_arr[0] = [
				'name' => 'Dashboard',
				'path' => null
			];
			break;
		case 'project':
			$breadcrumb_arr[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'action':
			$project = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$breadcrumb_arr[] = [
				'name' => $project,
				'path' => 'project/' . $project
			];

			$breadcrumb_arr[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'meeting':
			$action = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$project = substr($action, 0, strrpos($action, '-'));

			$breadcrumb_arr[] = [
				'name' => $project,
				'path' => 'project/' . $project
			];

			// $breadcrumb_arr[] = [
			// 	'name' => $action,
			// 	'path' => 'action/' . $action
			// ];

			$breadcrumb_arr[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;
		case 'agenda':
			$step = substr($this->uri->segment(2), 0, strrpos($this->uri->segment(2), '-'));
			$action = substr($step, 0, strrpos($step, '-'));
			$project = substr($action, 0, strrpos($action, '-'));

			$breadcrumb_arr[] = [
				'name' => $project,
				'path' => 'project/' . $project
			];

			// $breadcrumb_arr[] = [
			// 	'name' => $action,
			// 	'path' => 'action/' . $action
			// ];

			$breadcrumb_arr[] = [
				'name' => $step,
				'path' => 'step/' . $step
			];

			$breadcrumb_arr[] = [
				'name' => $this->uri->segment(2),
				'path' => null
			];
			break;

		default: 
			if (isset($breadcrumb) && is_array($breadcrumb)) {
				$breadcrumb_arr = array_merge($breadcrumb_arr, $breadcrumb);
			}

			break;
	}
}
?>
				<div class="an-breadcrumb wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
					<ol class="breadcrumb">
						<?php 
						foreach ($breadcrumb_arr as $item) :
							if (isset($item['path']) && !is_null($item['path'])) :
						?>
						<li><a href="<?php echo site_url($item['path']); ?>"><?php echo $item['name']; ?></a></li>
						<?php
							else:
						?>
						<li class="active"><?php echo $item['name']; ?></li>
						<?php
							endif;
						endforeach;
						?>
					</ol>
				</div>