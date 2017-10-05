<?php

/**
* Generate invitation
* Author: viethd
*/
class Invitation
{
	private $current_user;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->lang->load('invite/invite');
		$this->ci->load->library('domain');
		$this->ci->load->model('roles/role_model');
		$this->ci->load->model('project/project_model');
		$this->ci->load->model('project/project_member_model');
		$this->ci->load->model('project/project_member_invite_model');
		$this->ci->load->model('invite/user_invite_model');
		$this->ci->load->library('emailer/emailer');
		$this->ci->load->library('mb_project');
		$this->ci->load->library('users/auth');
		$this->ci->load->library('parser');

		$this->current_user = $this->ci->auth->user();
	}

	public function send_invitation($object_type, $object_id, $email, $invite_role = null) {
		switch ($object_type) {
		case 'organization': 
			return $this->send_organization_invitation($email, $invite_role);
		case 'project':
			return $this->send_project_invitation($object_id, $email);
			break;
		case 'meeting':
			break;
		default: return false;
		}
	}

	public function send_organization_invitation($email, $invite_role)
	{
		// Did we invite this user?
		$invitation = $this->ci->user_invite_model
		->select('o.name AS organization_name, avatar, invite_role, email, 
		CONCAT(u.first_name, " ", u.last_name) AS inviter_name, o.organization_id, status', false)
		->join('organizations o', 'user_invite.organization_id = o.organization_id')
		->join('users u', 'user_invite.invited_by = u.user_id')
		->where([
			'invite_email' => $email, 
			'o.organization_id' => $this->current_user->current_organization_id
		])
		->limit(1)
		->find_all();
		
		if ($invitation !== false) {
			return lang('iv_user_already_invited');
		}

		$invitation = $this->ci->user_model
		->select('o.name AS organization_name, CONCAT(first_name, " ", last_name) AS inviter_name, avatar', false)
		->join('user_to_organizations uto', 'users.user_id = uto.user_id')
		->join('organizations o', 'uto.organization_id = o.organization_id AND o.organization_id = "' . $this->current_user->current_organization_id . '"')
		->limit(1)
		->find($this->current_user->user_id);

		do {
			$invite_code = $this->generateRandomString(64);
		} while ($this->ci->user_invite_model->count_by('invite_code', $invite_code) > 0);

		$pass_link = site_url("users/invitation/organization/{$invite_code}");

		// Now send the email
		
		// $data = array(
		// 	'to'	  => $email,
		// 	'subject' => lang('iv_meeting_bullet_invitation'),
		// 	'message' => $this->ci->load->view(
		// 		'invite/invitation_template',
		// 		array(
		// 			'link' => $pass_link,
		// 			'organization_name' => $invitation->organization_name,
		// 			'inviter_name' => $invitation->inviter_name,
		// 			'avatar' => $invitation->avatar,
		// 			'email' => $email,
		// 		),
		// 		true
		// 	),
		// );
		
		$template = $this->ci->db->where('email_template_key', 'INVITE_USER_TO_ORGANIZATION')
							->where('language_code', 'en_US')
							->get('email_templates')->row();
		if (empty($template)) {
			return lang('iv_unknown_error');
		}

		$data = array(
			'to'	  => $email,
			'subject' => $template->email_title,
			'message' => $this->ci->parser->parse_string($template->email_title, [
				'LINK' => $pass_link,
				'ORGANIZATION_NAME' => $invitation->organization_name,
				'INVITER_NAME' => $invitation->inviter_name,
				'AVATAR' => strstr($invitation->avatar, 'http') ? $invitation->avatar : img_path() . 'users/'. $invitation->avatar,
				'EMAIL' => $email,
			], true)
		);

		$this->ci->emailer->send($data, TRUE);

		// Save invitation
		$query = $this->ci->user_invite_model->insert([
			'invite_email' => $email,
			'organization_id' => $this->current_user->current_organization_id,
			'invite_code' => $invite_code,
			'invite_role' => $invite_role,
			'invited_by' => $this->current_user->user_id
		]);

		if ($query) {
			return 1;
		}

		return lang('iv_unknown_error');
	}

	public function send_project_invitation($project_id, $email, $invited_emails = [])
	{
		$email_template = $this->ci->db->where('email_template_key', 'INVITE_USER_TO_PROJECT')
								->where('language_code', 'en_US')
								->get('email_templates')->row();
		if (empty($email_template)) {
			return false;
		}


		// Is project valid?
		$project = $this->ci->project_model
		->select('projects.name, cost_code, avatar, email, 
		CONCAT(first_name, " ", last_name) AS full_name,
		o.name AS organization_name, 
		o.url AS organization_domain', false)
		->join('users u', 'u.user_id = owner_id')
		->join('organizations o', 'o.organization_id = projects.organization_id')
		->limit(1)
		->find($project_id);

		if ( ! $project) {
			return lang('iv_project_not_found');
		}

		do {
			$invite_code = $this->generateRandomString(64);
		} while ($this->ci->user_invite_model->count_by('invite_code', $invite_code) > 0);

		$invitation_url = site_url("users/invitation/project/$invite_code/");

		// Now send the email
		$data = array(
			'to'	  => $email,
			'subject' => $this->ci->parser->parse_string($email_template->email_title, ['PROJECT_NAME' => $project->name], true),
			'message' => $this->ci->parser->parse_string($email_template->email_template_content, [
				'SITE_URL' => site_url(),
				'ACCEPT_INVITATION_URL' => $invitation_url . 'accept',
				'DECLINE_INVITATION_URL' => $invitation_url . 'decline',

				'INVITER_NAME' => $this->current_user->first_name . ' ' . $this->current_user->last_name,
				'INVITER_AVATAR_URL' => avatar_url($this->current_user->avatar, $this->current_user->email, 128),
				'INVITER_PROFILE_URL' => '#',

				'INVITEE_EMAIL' => $email,

				'PROJECT_NAME' => $project->name,
				'PROJECT_CODE' => $project->cost_code,
				'OWNER_NAME' => $project->full_name,
				'OWNER_AVATAR_URL' => avatar_url($project->avatar, $project->email), 
				'ORGANIZATION_NAME' => $project->organization_name,
				'ORGANIZATION_URL' => (is_https() ? 'https://' : 'http://') . 
				$project->organization_domain . '.' . $this->ci->domain->get_main_domain(),
			], true)
		);

		$this->ci->emailer->send($data, TRUE);

		// Save invitation
		$query = $this->ci->project_member_invite_model->insert([
			'invite_email' => $email,
			'project_id' => $project_id,
			'invite_code' => $invite_code,
			'invited_by' => $this->current_user->user_id
		]);

		if ($query) {
			return 1;
		}

		return lang('iv_unknown_error');
	}

	/*
		Invitation code generator
		Grabs from http://stackoverflow.com/questions/4356289/php-random-string-generator
	*/
	public function generateRandomString($length = 10) {
		$characters = '0123456789QWERTYUIOPASDFGHJKLZXCVBNM';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}