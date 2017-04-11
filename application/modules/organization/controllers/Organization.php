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
		$result = $this->check_current_email();

		if (isset($_POST['create'])) {
			$rules = $this->organization_model->get_validation_rules();
			$this->form_validation->set_rules($rules['create_organization']);

			if ($this->form_validation->run() !== false) {
				$this->load->helper('text');
				$url = convert_accented_characters($this->input->post('url'));
				$url = preg_replace("/[^a-z0-9]+/", "_", $url);

				$data = [
					'name' => $this->input->post('name'),
					'url' => $url,
				];

				if ($result['public_domain']) {
					$data['signup_mode'] = 'INVITE';
				}

				$this->db->trans_begin();
				$organization_added = $this->organization_model->insert($data);
				$error = true;
				if ($organization_added) {
					$organization_id = $organization_added;

					$user_role_added = $this->db->insert('user_to_organizations', [
						'user_id' => $this->current_user->user_id,
						'organization_id' => $organization_id,
						'role_id' => 1,
					]);
					if ($user_role_added) {
						$error = false;
					}

					if ($result['public_domain'] === false && $error === false) {
						$organization_domain_added = $this->db->insert('organization_domains', [
							'organization_id' => $organization_id,
							'domain' => $result['domain_name']
						]);
						if (! $organization_domain_added) {
							$error = true;
						}
					}
				}

				if ($this->db->trans_status() === FALSE) {
					$error = true;
					$this->db->trans_rollback();
				} else {
					if (! $error) {
						$this->db->trans_commit();
					} else {
						$this->db->trans_rollback();
					}
				}

				if (! $error) {
					Template::set_message(lang('org_create_success'), 'success');
					redirect('/'); // new organization url
				} else {
					Template::set_message(lang('org_create_fail'), 'danger');
				}
			}
		}

		Assets::add_module_js('organization', 'organization.js');
		Template::render('organization');
	}

	private function check_current_email()
	{
		$data = [
			'public_domain' => false,
			'domain_name' => ''
		];

		$allow_create_organization = false;
		$current_email = $this->current_user->email;
		$domain_name = $domain_name = substr(strrchr($current_email, "@"), 1);

		$data['domain_name'] = $domain_name;

		$is_public_domain_name = $this->db->select('count(*) as count')
										->where('domain', $domain_name)
										->get('public_email_domains')->row()->count > 0 ? true : false;
		if ($is_public_domain_name) {
			$data['public_domain'] = true;
			$user_organization = $this->db->select('o.url')
											->from('organizations o')
											->join('user_to_organizations uto', 'o.organization_id = uto.organization_id', 'left')
											->where('uto.user_id', $this->current_user->user_id)
											->get()->row();
			if (! empty($user_organization)) {
				Template::set_message(lang('org_already_belong_to_other'), 'info');
				redirect('/');
			}
		} else {
			$existed_domain_name = $this->db->select('od.*, o.url')
											->from('organization_domains od')
											->join('organizations o', 'o.organization_id = od.organization_id', 'left')
											->where('od.domain', $domain_name)
											->get()->result();
			if (is_array($existed_domain_name) && count($existed_domain_name) > 0) {
				Template::set_message(lang('org_already_belong_to_other'), 'info');
				redirect('/');
			}
		}

		return $data;
	}
}
