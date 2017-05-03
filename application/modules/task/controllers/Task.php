<?php defined('BASEPATH') || exit('No direct script access allowed');

class Task extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('task_model');
		$this->lang->load('task');
	}

	public function create($action_key)
	{
		// if (! $this->input->is_ajax_request()) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }
		$this->load->model('action/action_model');
		$this->load->helper('mb_form');

		$action_id = $this->action_model->get_action_id($action_key, $this->current_user);

		if (!$action_id) {
			Template::set('message', lang('tk_not_have_permission'));
			Template::set('message_type', 'danger');
		} else {
			if ($this->input->post()) {
				$rules = $this->task_model->get_validation_rules();
				$this->form_validation->set_rules($rules['create']);

				if ($this->form_validation->run() !== false) {
					$data = $this->task_model->prep_data($this->input->post());
					
					$task_id = $this->task_model->insert($data);
					if ($task_id) {
						Template::set('message', lang('tk_create_task_success'));
						Template::set('message_type', 'success');
					} else {
						$error = true;
					}
				}
			} else {
				$error = true;
			}

			if (! empty($error)) {
				Template::set('message', lang('tk_create_task_fail'));
				Template::set('message_type', 'danger');
				Template::set('close_modal', 0);
			}
		}

		Template::render();
	}
}