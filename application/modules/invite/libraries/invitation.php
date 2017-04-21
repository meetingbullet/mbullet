<?php

/**
* Generate invitation
* Author: viethd
*/
class Invitation
{
	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->lang->load('invite');
		$this->ci->load->model('users/user_model');
		$this->ci->load->model('user_invite_model');
	}

	public function generate($email, $invite_role, $current_user)
	{
		// Did we invite this user?
		$invitation = $this->ci->user_invite_model
								->select('o.name AS organization_name, CONCAT(u.first_name, " ", u.last_name) AS inviter_name, avatar, invite_role, email, o.organization_id, status', false)
								->join('organizations o', 'user_invite.organization_id = o.organization_id')
								->join('users u', 'user_invite.invited_by = u.user_id')
								->where(['invite_email' => $email, 'o.organization_id' => $current_user->current_organization_id])
								->limit(1)
								->find_all();
		
		if ($invitation !== false) {
			return lang('iv_user_already_invited');
		}

		$invitation = $this->ci->user_model
								->select('o.name AS organization_name, CONCAT(first_name, " ", last_name) AS inviter_name, avatar', false)
								->join('user_to_organizations uto', 'users.user_id = uto.user_id')
								->join('organizations o', 'uto.organization_id = o.organization_id')
								->limit(1)
								->find($current_user->user_id);



		do {
			$invite_code = $this->generateRandomString(64);
		} while ($this->ci->user_invite_model->count_by('invite_code', $invite_code) > 0);

		$pass_link = site_url("users/invitation/{$invite_code}");

		// Now send the email
		$this->ci->load->library('emailer/emailer');
		$data = array(
			'to'	  => $email,
			'subject' => lang('iv_meeting_bullet_invitation'),
			'message' => $this->ci->load->view(
				'invitation_template',
				array(
					'link' => $pass_link,
					'organization_name' => $invitation->organization_name,
					'inviter_name' => $invitation->inviter_name,
					'avatar' => $invitation->avatar,
					'email' => $email,
				),
				true
			),
		);

		$this->ci->emailer->send($data, TRUE);

		// Save invitation
		$query = $this->ci->user_invite_model->insert([
			'invite_email' => $email,
			'organization_id' => $current_user->current_organization_id,
			'invite_code' => $invite_code,
			'invite_role' => $invite_role,
			'invited_by' => $current_user->user_id
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
	private function generateRandomString($length = 10) {
		$characters = '0123456789QWERTYUIOPASDFGHJKLZXCVBNM';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}