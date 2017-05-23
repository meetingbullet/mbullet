<?php defined('BASEPATH') || exit('No direct script access allowed');

class Team extends Authenticated_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->lang->load('invite/invite');
		$this->load->helper('mb_form_helper');
		$this->load->library('invite/invitation');
		$this->load->model('invite/user_invite_model');
		$this->load->model('roles/role_model');
		$this->load->model('invite/user_to_organizations_model');
		Assets::add_module_js('admin', 'invite.js');
		$this->lang->load('project/project');
		$this->lang->load('admin/admin');
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
			$pagination_config['total_rows'] = $this->user_model->get_organization_members($this->current_user->current_organization_id, 'COUNT(*) as count')[0]->count;
		}

		if ($this->input->get('type') == 'disabled') {
			$pagination_config['total_rows'] = $this->user_model->get_organization_members($this->current_user->current_organization_id, 'COUNT(*) as count', false, ['enabled' => 0])[0]->count;
		}

		if ($this->input->get('type') == 'by_role') {
			$role_id = $this->input->get('role_id');
			if (empty($role_id)) {
				$role_id = $roles[0]->role_id;
			}
			$pagination_config['total_rows'] = $this->user_model->get_organization_members($this->current_user->current_organization_id, 'COUNT(*) as count', false, ['uto.role_id' => $role_id])[0]->count;
		}

		$this->pagination->initialize($pagination_config);
		$users_list['pagination'] = $this->pagination->create_links();

		$current_page = $this->input->get('page');
		if (empty($current_page)) {
			$current_page = 1;
		}
		$offset = ($current_page - 1) * $limit;

		if ($this->input->get('type') == 'all' || empty($this->input->get('type'))) {
			$users = $this->user_model->get_organization_members($this->current_user->current_organization_id, 'uto.user_id, email, first_name, last_name, avatar, last_login, uto.enabled, r.name as role_name, r.role_id, r.is_public', true, [], $limit, $offset);
		}

		if ($this->input->get('type') == 'disabled') {
			$users = $this->user_model->get_organization_members($this->current_user->current_organization_id, 'uto.user_id, email, first_name, last_name, avatar, last_login, uto.enabled, r.name as role_name, r.role_id, r.is_public', true, ['enabled' => 0], $limit, $offset);
		}

		if ($this->input->get('type') == 'by_role') {
			$users = $this->user_model->get_organization_members($this->current_user->current_organization_id, 'uto.user_id, email, first_name, last_name, avatar, last_login, uto.enabled, r.name as role_name, r.role_id, r.is_public', true, ['uto.role_id' => $role_id], $limit, $offset);
		}

		$users_list['result'] = sprintf(lang('ad_tm_pager_result'), ($offset + 1), ($offset + count($users)), $pagination_config['total_rows']);
		if (empty($users)) {
			$users = [];
			$users_list['result'] = '';
		}
		$users_list['data'] = $users;

		Template::set('roles', $roles);
		Template::set('users_list', $users_list);
		Template::render();
	}

	public function invite()
	{
		Template::set('close_modal', 0);
		Template::set('message_type', null);
		Template::set('message', '');

		$roles = $this->role_model->select('role_id, name, description, join_default')->where('organization_id', $this->current_user->current_organization_id)->find_all();
		Template::set('roles', $roles);
		

		if (isset($_POST['add'])) {
			$message = $this->invitation->generate($this->input->post('email'), $this->input->post('invite_role'), $this->current_user);

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
}