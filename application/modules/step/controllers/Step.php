<?php defined('BASEPATH') || exit('No direct script access allowed');

class Step extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('step_model');
		$this->lang->load('step');
	}

	public function detail($step_key = null)
	{
		if (empty($step_key)) {
			redirect('/dashboard');
		}

		$keys = explode('-', $step_key);
		if (empty($keys) || count($keys) < 3) {
			redirect('/dashboard');
		}

		$project_key = $keys[0];
		$action_key = $keys[0] . '-' . $keys[1];

		$this->load->model('projects/project_model');
		$project_id = $this->project_model->get_project_id($project_key, $this->current_user);

		$step = $this->step_model->select('steps.*, CONCAT(u.first_name, u.last_name) as owner_name')
								->join('users u', 'u.user_id = steps.owner_id', 'left')
								->find_by('step_key', $step_key);

		$this->load->model('task/task_model');
		$tasks = $this->task_model->select('tasks.*, CONCAT(u.first_name, u.last_name) as owner_name')
								->join('users u', 'u.user_id = tasks.owner_id', 'left')
								->where('step_id', $step->step_id)->find_all();

		if (! class_exist('User_model')) {
			$this->load->model('users', 'user_model');
		}
		$oragnization_members = $this->user_model->get_organization_members($this->current_user->current_organization_id);

		Assets::add_module_css('step', 'step.css');
		Assets::add_module_js('step', 'step.js');
		Assets::add_js($this->load->view('detail_js', [
			'oragnization_members' => $oragnization_members,
			'step' => $step
		], true), 'inline');
		Template::set('step', $step);
		Template::set('tasks', $tasks);
		Template::set('project_key', $project_key);
		Template::set('action_key', $action_key);
		Template::set('step_key', $step_key);
		Template::render();
	}

	public function update_status($step_key = null)
	{
		if (! $this->input->is_ajax_request()) {
			redirect('/dashboard');
		}

		if (empty($step_key)) {
			if (! $this->input->is_ajax_request()) {
				redirect('/dashboard');
			} else {
				echo json_encode([
					'message_type' => 'danger',
					'message' => lang('st_update_status_fail')
				]);
				exit;
			}
		}

		$step_id = $this->step_model->get_step_id($step_key, $this->current_user);
		if (! $step_id) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		$buttons = [
			'open' => [
				'icon' => 'ion-ios-play',
				'label' => lang('st_start_step'),
				'next_status' => 'in-progress',
			],
			'in-progress' => [
				'icon' => 'ion-android-done',
				'label' => lang('st_ready'),
				'next_status' => 'ready-for-review',
			],
			'ready-for-review' => [
				'icon' => 'ion-android-done-all',
				'label' => lang('st_resolve'),
				'next_status' => 'resolved',
			],
			'resolved' => [
				'icon' => 'ion-ios-book',
				'label' => lang('st_reopen'),
				'next_status' => 'open',
			]
		];

		$status = $this->input->get('status');
		$updated = $this->step_model->update($step_id, [
										'status' => $status
									]);
		if (! $updated) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('st_update_status_fail')
			]);
			exit;
		}

		echo json_encode([
			'message_type' => 'success',
			'message' => lang('st_update_status_success'),
			'data' => array_merge($buttons[$status], ['url' => current_url() . '?status=' . urlencode($buttons[$status]['next_status'])])
		]);
		exit;
	}
}