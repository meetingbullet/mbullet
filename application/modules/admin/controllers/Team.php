<?php defined('BASEPATH') || exit('No direct script access allowed');

class Team extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('invite/invite');
		$this->lang->load('project/project');
		$this->lang->load('admin/admin');

		$this->load->library('invite/invitation');
		$this->load->helper('mb_form_helper');
		$this->load->model('roles/role_model');
		$this->load->model('invite/user_invite_model');
		$this->load->model('organization/organization_model');
		$this->load->model('invite/user_to_organizations_model');

		Assets::add_module_js('admin', 'invite.js');
		Assets::add_module_css('admin', 'invite.css');
	}

	public function index()
	{
		$this->auth->restrict('User.Team.View');
		$users_list = [
			'all' => [
				'data' => [],
				'pagination' => []
			],
			'disabled' => [
				'data' => [],
				'pagination' => []
			],
			'by_role' => [
				'data' => [],
				'pagination' => []
			],
		];
		$roles = $this->role_model->get_organization_roles($this->current_user->current_organization_id, 'name, role_id');
		$limit = 25;

		$this->load->library('pagination');
		// general pagination config
		$pagination_config = [
			'base_url' => current_url(),
			'per_page' => $limit,
			'use_page_numbers' => true,
			'page_query_string' => true,
			'reuse_query_string' => true,
			'enable_query_strings' => true,
			'query_string_segment' => 'page',
			'full_tag_open' => '<ul class="pagination">',
			'full_tag_close' => '</ul>',
			'num_tag_open' => '<li>',
			'num_tag_close' => '</li>',
			'cur_tag_open' => '<li><a class="active">',
			'cur_tag_close' => '</a></li>',
			'prev_link' => '<span aria-hidden="true"><i class="ion-chevron-left"></i></span>',
			'prev_tag_open' => '<li>',
			'prev_tag_close' => '</li>',
			'next_link' => '<span aria-hidden="true"><i class="ion-chevron-right"></i></span>',
			'next_tag_open' => '<li>',
			'next_tag_close' => '</li>',
			'last_link' => lang('pj_info_pager_last'),
			'last_tag_open' => '<li>',
			'last_tag_close' => '</li>',
			'first_link' => lang('pj_info_pager_first'),
			'first_tag_open' => '<li>',
			'first_tag_close' => '</li>'
		];

		if ($this->input->get('type') == 'all' || empty($this->input->get('type'))) {
			$pagination_config['total_rows'] = $this->user_model->get_organization_users($this->current_user->current_organization_id, 'COUNT(*) as count', false, [])[0]->count;
		}

		if ($this->input->get('type') == 'disabled') {
			$pagination_config['total_rows'] = $this->user_model->get_organization_users($this->current_user->current_organization_id, 'COUNT(*) as count', false, ['enabled' => 0])[0]->count;
		}

		if ($this->input->get('type') == 'by_role') {
			$role_id = $this->input->get('role_id');
			if (empty($role_id)) {
				$role_id = $roles[0]->role_id;
			}
			$pagination_config['total_rows'] = $this->user_model->get_organization_users($this->current_user->current_organization_id, 'COUNT(*) as count', false, ['uto.role_id' => $role_id])[0]->count;
		}

		$this->pagination->initialize($pagination_config);
		$users_list['pagination'] = $this->pagination->create_links();

		$current_page = $this->input->get('page');
		if (empty($current_page)) {
			$current_page = 1;
		}
		$offset = ($current_page - 1) * $limit;

		if ($this->input->get('type') == 'all' || empty($this->input->get('type'))) {
			$users = $this->user_model->get_organization_users($this->current_user->current_organization_id, 'uto.user_id, uto.title, uto.cost_of_time, email, first_name, last_name, avatar, last_login, uto.enabled, r.name as role_name, r.role_id, r.is_public', true, [], $limit, $offset);
		}

		if ($this->input->get('type') == 'disabled') {
			$users = $this->user_model->get_organization_users($this->current_user->current_organization_id, 'uto.user_id, uto.title, uto.cost_of_time, email, first_name, last_name, avatar, last_login, uto.enabled, r.name as role_name, r.role_id, r.is_public', true, ['enabled' => 0], $limit, $offset);
		}

		if ($this->input->get('type') == 'by_role') {
			$users = $this->user_model->get_organization_users($this->current_user->current_organization_id, 'uto.user_id, uto.title, uto.cost_of_time, email, first_name, last_name, avatar, last_login, uto.enabled, r.name as role_name, r.role_id, r.is_public', true, ['uto.role_id' => $role_id], $limit, $offset);
		}

		$users_list['result'] = sprintf(lang('ad_tm_pager_result'), ($offset + 1), ($offset + count($users)), $pagination_config['total_rows']);
		if (empty($users)) {
			$users = [];
			$users_list['result'] = '';
		}
		$users_list['data'] = $users;

		$organization = $this->organization_model->find($this->current_user->current_organization_id);
		
		Template::set('organization', $organization);
		Template::set('roles', $roles);
		Template::set('users_list', $users_list);
		Template::render();
	}

	public function invite()
	{
		Template::set('close_modal', 0);
		Template::set('message_type', null);
		Template::set('message', '');

		$roles = $this->role_model
		->select('role_id, name, description, join_default')
		->where('organization_id', $this->current_user->current_organization_id)
		->find_all();

		Template::set('roles', $roles);
		
		if (isset($_POST['add'])) {
			$message = $this->invitation->send_invitation(
				'organization', 
				null, 
				$this->input->post('email'), 
				$this->input->post('invite_role')
			);

			if ($message === 1) {
				Template::set('close_modal', 1);
				Template::set('message_type', 'success');
				Template::set('message', lang('iv_invitation_sent'));
				Template::set_message(lang('iv_invitation_sent'), 'success');

				// Just to reduce AJAX request size
				if ($this->input->is_ajax_request()) {
					Template::set('content', '');
				}
			} else {
				Template::set('message_type', 'danger');
				Template::set('message', $message);
				Template::set_message($message, 'danger');
			}
		}
		Template::render();
	}

	public function edit_user($user_id)
	{
		
		if (! $this->input->is_ajax_request()) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if (empty($user_id)) {
			Template::set('close_modal', 1);
			Template::set('message_type', 'danger');
			Template::set('message', lang('ad_tm_user_not_found'));
		}

		$roles = $this->role_model->get_organization_roles($this->current_user->current_organization_id, 'name, role_id');
		if (empty($roles)) {
			Template::set('close_modal', 1);
			Template::set('message_type', 'danger');
			Template::set('message', lang('ad_tm_role_not_found'));
		}
		// dump($this->db->last_query());

		$temp = [];
		foreach ($roles as $role) {
			$temp[$role->role_id] = $role->name;
		}
		$roles = $temp;
		$user = $this->user_model->select('users.*, uto.organization_id,  r.is_public, r.name, uto.role_id, uto.title, uto.cost_of_time, uto.enabled, CONCAT(first_name, " ", last_name) as full_name')
								->join('user_to_organizations uto', 'uto.user_id = users.user_id', 'left')
								->join('roles r', 'r.role_id = uto.role_id')
								->find($user_id);
		if (empty($user)) {
			Template::set('close_modal', 1);
			Template::set('message_type', 'danger');
			Template::set('message', lang('ad_tm_user_not_found'));
		}
		$permissions = $this->user_model->get_permission_edit($this->current_user->user_id, $user->organization_id);

		$disable = true;
		if ($permissions) {
			foreach($permissions as $permission) {
				if ($permission->manage_role_id == $user->role_id) {
					$disable = false;
					break;
				}
			}
		}

		if ($this->input->post()) {
			$disable_rule = [
				'field' => 'role_id',
				'label' => 'lang:ad_tm_role',
				'rules' => '',
			];
			$normal_rule = [
				'field' => 'role_id',
				'label' => 'lang:ad_tm_role',
				'rules' => 'trim|required|numeric',
			];
			$this->form_validation->set_rules([
				($disable) ? $disable_rule : $normal_rule,
				[
					'field' => 'cost_of_time',
					'label' => 'lang:ad_tm_cost_of_time',
					'rules' => 'trim|required|numeric',
				],
				[
					'field' => 'title',
					'label' => 'lang:ad_tm_title',
					'rules' => 'trim|max_length[255]',
				],
				[
					'field' => 'enabled',
					'label' => 'lang:ad_tm_enabled',
					'rules' => 'trim|numeric',
				]
			]);
			if ($this->form_validation->run() !== false) {
				$data['role_id'] = $this->input->post('role_id');
				$data['title'] = empty($this->input->post('title')) ? null : $this->input->post('title');
				$data['cost_of_time'] = $this->input->post('cost_of_time');
				$data['enabled'] = $this->input->post('enabled');
				if (empty($this->input->post('enabled'))) {
					$data['enabled'] = 0;
				}
				if ($disable) {
					$data['role_id'] = $user->role_id;
				}
				$updated = $this->user_to_organizations_model->where('organization_id', $this->current_user->current_organization_id)->skip_validation(true)->update($user_id, $data);
				if ($updated) {
					if ($data['enabled'] != $user->enabled) {
						$this->notify_user_status($user->email, $user->full_name, $data['enabled']);
					}
					Template::set('close_modal', 1);
					Template::set('message_type', 'success');
					Template::set('message', lang('ad_tm_update_success'));
				} else {
					Template::set('close_modal', 0);
					Template::set('message_type', 'danger');
					Template::set('message', lang('ad_tm_update_fail'));
				}
			} else {
				Template::set('close_modal', 0);
				Template::set('message_type', 'danger');
				Template::set('message', validation_errors());
			}
		}
		Template::set('disable', $disable);
		Template::set('permissions', $permissions);
		Template::set('roles', $roles);
		Template::set('user', $user);
		if ($this->input->is_ajax_request()) {
			Template::render('ajax');
		} else {
			Template::render();
		}
	}

	private function notify_user_status($email, $fullname, $status)
	{
		if (is_null($status)) {
			return false;
		}

		if ( $status == 0) {
			$template_key = 'ACCOUNT_DISABLED';
		} elseif ($status == 1) {
			$template_key = 'ACCOUNT_ENABLED';
		} else {
			return false;
		}

		$owner = $this->user_model->select('CONCAT(first_name, " ", last_name) as full_name')
								->join('user_to_organizations uto', 'uto.user_id = users.user_id', 'left')
								->join('roles r', 'uto.role_id = r.role_id AND r.is_public = 1', 'left')
								->find_by('uto.organization_id', $this->current_user->current_organization_id);
		if (empty($owner)) {
			return false;
		}

		$email_template = $this->db->where('email_template_key', $template_key)
								->where('language_code', 'en_US')
								->get('email_templates')->row();
		if (empty($email_template)) {
			return false;
		}

		$this->load->library('emailer/emailer');
		$this->load->library('parser');

		$email_data = [
			'to' => $email,
			'subject' => $email_template->email_title,
			'message' => $this->parser->parse_string(html_entity_decode($email_template->email_template_content), [
							'OWNER_NAME' => $this->current_user->first_name . ' ' . $this->current_user->last_name,
							'USER_NAME' => $fullname
						], true)
		];
		return $this->emailer->send($email_data, true);
	}
}