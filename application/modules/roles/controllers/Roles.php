<?php defined('BASEPATH') || exit('No direct script access allowed');

class Roles extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('mb_form');
		$this->load->model('role_model');
		$this->lang->load('roles');
		$this->auth->restrict('Role.Team.View');

		Assets::add_module_js('roles', 'roles.js');
	}

	public function index()
	{
		$roles = $this->role_model->where('organization_id', $this->current_user->current_organization_id)->find_all();

		Assets::add_js($this->load->view('index_js', null, true), 'inline');
		Template::set('current_role_id', $this->current_user->role_ids[$this->current_user->current_organization_id]);
		Template::set('roles', $roles);
		Template::set('breadcrumb', [['name' => lang('rl_roles')]]);
		Template::render();
	}

	public function create($role_id = null)
	{
		// Though Edit role reuses Create functionality, Its Template view still set to Edit.php, 
		// We have to manually set view to Create.php
		Template::set_view('create');
		Template::set('close_modal', 0);

		if ($role_id === null) {
			if (! has_permission('Role.Team.Create')) {
				Template::set('message', lang('rl_you_have_not_earned_permission_to_create_role') );
				Template::set('message_type', 'danger');
				Template::render();
				return;
			}
		} else {
			// Cannot edit the role which user is currently inside
			if (! has_permission('Role.Team.Edit') || $role_id == $this->current_user->role_ids[$this->current_user->current_organization_id]) {
				Template::set('message', lang('rl_you_have_not_earned_permission_to_edit_role') );
				Template::set('message_type', 'danger');
				Template::render();
				return;
			}
		}

		if (isset($_POST['save'])) {
			if ( ! $data = $this->save_role($role_id) ) {
				Template::set('message', lang('rl_unable_to_create_role') );
				Template::set('message_type', 'danger');
			} else {
				if ($role_id) {
					Template::set('message', sprintf(lang('rl_role_updated'), $data->name) );
				} else {
					Template::set('message', lang('rl_role_created') );
				}

				Template::set('data', $data);
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
			}
		}

		if (is_numeric($role_id) ) {
			$role = $this->role_model->select('role_id, name, description, join_default')->find($role_id);

			if ( ! $role) {
				Template::set('message', lang('rl_cannot_find_the_role') );
				Template::set('message_type', 'danger');
				Template::render();
				return;
			}

			Template::set('role', $role);
		}


		
		Template::render();
	}

	public function edit($role_id = null)
	{
		if ( ! $role_id) {
			Template::set('message', lang('rl_you_have_not_earned_permission_to_create_role') );
			Template::set('message_type', 'danger');
			Template::set_view('create');
			Template::render();
			return;
		}
		
		$this->create($role_id);
	}

	public function delete($role_id = null)
	{
		if ( ! has_permission('Role.Team.Delete') ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('rl_you_have_not_earned_permission_to_delete_role')
			]);
			return;
		}

		if ( ! is_numeric($role_id)) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('rl_cannot_find_the_role')
			]);
			return;
		}
		
		if ($role_id == $this->current_user->role_ids[$this->current_user->current_organization_id]) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('rl_cannot_delete_the_role_which_you_are_in')
			]);
			return;
		}

		$role = $this->role_model->select('name')->limit(1)->find($role_id);

		if (! $role) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('rl_cannot_find_the_role')
			]);
			return;
		}

		if ($this->role_model->delete($role_id)) {
			echo json_encode([
				'message_type' => 'success',
				'message' => sprintf(lang('rl_role_x_has_been_deleted'), $role->name)
			]);
			return;
		}

		echo json_encode([
			'message_type' => 'danger',
			'message' => lang('rl_unknown_error')
		]);
		return;
	}

	private function save_role($role_id)
	{
		// Create new role
		if ($role_id === null) {
			$data = $this->role_model->prep_data($this->input->post());
			$data['organization_id'] = $this->current_user->current_organization_id;

			if ($this->input->post('join_default') == 'on') {
				$data['join_default'] = 1;
				$this->role_model->update_where('organization_id', $this->current_user->current_organization_id, ['join_default' => 0]);
			} else {
				$data['join_default'] = 0;
			}

			$role_id = $this->role_model->insert($data);

			// Validation or DB errors
			if (! $role_id) {
				return false;
			} 
		} else {
			// Edit role
			$data = $this->role_model->prep_data($this->input->post());

			if ($this->input->post('join_default') == 'on') {
				$data['join_default'] = 1;
				$this->role_model->update_where('organization_id', $this->current_user->current_organization_id, ['join_default' => 0]);
			} else {
				$data['join_default'] = 0;
			}

			$this->role_model->update($role_id, $data);

			// Validation or DB errors
			if (! $role_id) {
				return false;
			} 
		}

		return $this->role_model->select('role_id, name, description, join_default')->limit(1)->find($role_id);
	}
}