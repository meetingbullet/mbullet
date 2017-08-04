<?php defined('BASEPATH') || exit('No direct script access allowed');
class Permissions extends Authenticated_Controller 
{
	public function __construct() {
		parent::__construct();
		$this->lang->load('permissions');
		$this->load->helper('mb_form_helper');
		$this->load->model('permissions_model');
		$this->load->model('permission_manage_model');
		$this->load->model('roles/role_model');
		$this->load->model('role_to_permissions_model');
		Assets::add_js($this->load->view('permissions/index_js', null, true), 'inline');
	}
	public function index(){
		Template::set('breadcrumb', [
			[ 'name' => lang('pm_permissions') ] ,
		]);
		Template::set('permissions', $this->permissions_model->order_by('name')->find_all());
		Template::set('roles', $this->role_model->get_organization_roles($this->current_user->current_organization_id));
		Template::set('relations', $this->permission_manage_model->get_manage_role($this->current_user->current_organization_id));
		Template::set('role_to_permission_relations', $this->role_to_permissions_model->get_role_to_permission_relation($this->current_user->current_organization_id));
		Template::render();
	}

	public function save_changes(){
		$changes = $this->input->post()['data'];
		$role_to_role = [];
		$role_to_permission = [];
		$owner_id = null;
		$roles = $this->role_model->get_organization_roles($this->current_user->current_organization_id);
		$permissions =  $this->permissions_model->order_by('name')->find_all();
		foreach ($roles as $role) {
			if ($role->is_public == 1 && $role->organization_id == null) {
				$owner_id = $role->role_id;
				foreach ($roles as $role2) {
					$tmp['role_id'] = $role->role_id;
					$tmp['manage_role_id'] = $role2->role_id;
					array_push($role_to_role, $tmp);
				}
			}
		}

		foreach ($roles as $role) {
			if ($role->is_public == 1 && $role->organization_id == null) {
				foreach ($permissions as $permission) {
					$tmp3['role_id'] = $role->role_id;
					$tmp3['permission_id'] = $permission->permission_id;
					array_push($role_to_permission, $tmp3);
				}
			}
		}
		foreach ($changes as $change) {
			$str = explode("-",$change);
			if ($str['2'] == "role" && $str['1'] != $owner_id && $str['3'] != $owner_id) {
				$tmp1['role_id'] = $str['1'];
				$tmp1['manage_role_id'] = $str['3'];
				array_push($role_to_role, $tmp1);
			}
			if ($str['2'] == "permission" && $str['1'] != $owner_id) {
				$tmp2['role_id'] = $str['1'];
				$tmp2['permission_id'] = $str['3'];
				array_push($role_to_permission, $tmp2);
			}
		} 

		$this->permission_manage_model->delete_role_to_role($this->current_user->current_organization_id);
		if ( !$this->db->insert_batch('permission_manage', $role_to_role ) ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('pm_error_while_update')
			]);
			return;
		}
		$this->role_to_permissions_model->delete_role_to_permission($this->current_user->current_organization_id);
		if ( !$this->db->insert_batch('role_to_permissions', $role_to_permission ) ) {
			echo json_encode([
				'message_type' => 'danger',
				'message' => lang('pm_error_while_update')
			]);
			return;
		}

		echo json_encode([
			'message_type' => 'success',
			'message' => lang('pm_save_complete')
		]);
		return;

	}
}
?>