<?php defined('BASEPATH') || exit('No direct script access allowed');

class Organization extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('organization_model');
		$this->lang->load('organization');
	}

	public function create()
	{
		// If user still access to an organization, can not create new anymore
		$orgs = $this->db->select('count(*) AS total')
							->from('organizations o')
							->join('user_to_organizations uo', 'o.organization_id = uo.organization_id', 'left')
							->where('uo.user_id', $this->current_user->user_id)
							->where('uo.enabled', 1)
							->get()->row();
		if ($orgs->total > 0) redirect($this->previous_page);
		
		// if has submited data
		if (isset($_POST['create'])) {
			//get validation rules
			$rules = $this->organization_model->get_validation_rules();
			$this->form_validation->set_rules($rules['create_organization']);
			// validate
			if ($this->form_validation->run() !== false) {
				$this->load->helper('text');
				// filter the url
				$url = convert_accented_characters($this->input->post('url'));
				$url = preg_replace("/[^a-z0-9]+/", "", $url);

				$data = [
					'name' => $this->input->post('name'),
					'url' => $url,
				];
				// decide signup mode base on type on domain name
				if ($this->check_current_email_result['public_domain']) {
					$data['signup_mode'] = 'INVITE';
				}

				try {
					$this->db->trans_begin();
					//create organization
					$organization_added = $this->organization_model->insert($data);

					if ($organization_added) {
						$organization_id = $organization_added;

						if (! class_exists('Role_model')) {
							$this->load->model('roles/role_model');
						}
						// add owner role for this user
						$owner_role = $this->role_model->select('role_id')->find_by('is_public', 1);
						if (! $owner_role) {
							throw new Exception('error position 1');
						}

						$user_role_added = $this->db->insert('user_to_organizations', [
							'user_id' => $this->current_user->user_id,
							'organization_id' => $organization_id,
							'role_id' => $owner_role->role_id,
						]);
						if (! $user_role_added) {
							throw new Exception('error position 2');
						}
						// get system default roles
						$system_default_roles = $this->role_model->select('role_id, name, description, join_default')->where('system_default', 1)->find_all();
						if (($system_default_roles === false) || (is_array($system_default_roles) && count($system_default_roles) == 0)) {
							throw new Exception('error position 3');
						}

						foreach ($system_default_roles as $role) {
							$role_id = $role->role_id;
							unset($role->role_id);
							$role->organization_id = $organization_id;
							// clone system default role to new organization
							$organization_role_added = $this->role_model->insert((array) $role);
							if (! $organization_role_added) {
								throw new Exception('error position 4');
							}
							$organization_role_id = $organization_role_added;
							// get system default role permissions
							$role_permissions = $this->db->select('permission_id')
														->where('role_id', $role_id)
														->get('role_to_permissions')
														->result();
							if (($role_permissions === false) || (is_array($role_permissions) && count($role_permissions) == 0)) {
								throw new Exception('error position 5');
							}

							$organization_role_permissions = [];
							foreach ($role_permissions as $permission) {
								$permission->role_id = $organization_role_id;
								$organization_role_permissions[] = (array) $permission;
							}
							// clone system default role permission to new organization
							$organization_role_permissions_added = $this->db->insert_batch('role_to_permissions', $organization_role_permissions);
							if (! $organization_role_permissions_added) {
								throw new Exception('error position 5');
							}
						}
						// if not public domain name email, insert organization domain name
						if ($this->check_current_email_result['public_domain'] === false) {
							$organization_domain_added = $this->db->insert('organization_domains', [
								'organization_id' => $organization_id,
								'domain' => $this->check_current_email_result['domain_name']
							]);
							if (! $organization_domain_added) {
								throw new Exception('error position 7');
							}
						}
					}

					if ($this->db->trans_status() === FALSE) {
						throw new Exception('error position 8');
					} else {
						$this->db->trans_commit();
					}
				} catch (Exception $e) {
					// if any errors occour, roll back the queries
					$catched = true;
					$this->db->trans_rollback();
				}

				if (! isset($catched)) {
					Template::set_message(lang('org_create_success'), 'success');

					$organization_url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $url . '.' . $_SERVER['SERVER_NAME'];
					redirect($organization_url);
				} else {
					Template::set_message(lang('org_create_fail'), 'danger');
				}
			}
		}

		Assets::add_module_js('organization', 'organization.js');
		Template::render('organization');
	}
}
