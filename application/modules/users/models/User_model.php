<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_model extends BF_Model
{
	protected $table_name	= 'users';
	protected $key			= 'user_id';
	protected $date_format	= 'datetime';

	protected $log_user	= false;
	protected $set_created	= true;
	protected $set_modified = false;
	protected $soft_deletes	= true;

	protected $created_field	 = 'created_on';
	protected $deleted_field	 = 'deleted';

	// Customize the operations of the model without recreating the insert,
	// update, etc. methods by adding the method names to act as callbacks here.
	protected $before_insert	= array();
	protected $after_insert	= array();
	protected $before_update	= array();
	protected $after_update	= array();
	protected $before_find		= array();
	protected $after_find		= array();
	protected $before_delete	= array();
	protected $after_delete	= array();

	// For performance reasons, you may require your model to NOT return the id
	// of the last inserted row as it is a bit of a slow method. This is
	// primarily helpful when running big loops over data.
	protected $return_insert_id = true;

	// The default type for returned row data.
	protected $return_type = 'object';

	// Items that are always removed from data prior to inserts or updates.
	protected $protected_attributes = array();

	// You may need to move certain rules (like required) into the
	// $insert_validation_rules array and out of the standard validation array.
	// That way it is only required during inserts, not updates which may only
	// be updating a portion of the data.
	protected $validation_rules		= array(
		'create_profile' => array (
			array(
				'field' => 'email',
				'label' => 'lang:us_reg_email',
				'rules' => 'trim|required|valid_email|max_length[255]|unique[users.email]',
			),
			array(
				'field' => 'confirm_terms',
				'label' => 'lang:us_reg_confirm_terms',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'password',
				'label' => 'lang:us_reg_password',
				'rules' => 'trim|required|max_length[60]',
			),
			array(
				'field' => 'conf_password',
				'label' => 'lang:us_reg_conf_password',
				'rules' => 'trim|required|max_length[60]|matches[password]',
			),
			array(
				'field' => 'first_name',
				'label' => 'lang:us_reg_first_name',
				'rules' => 'trim|required|max_length[255]',
			),
			array(
				'field' => 'last_name',
				'label' => 'lang:us_reg_last_name',
				'rules' => 'trim|required|max_length[255]',
			),
			array(
				'field' => 'skype',
				'label' => 'lang:us_reg_skype',
				'rules' => 'trim|max_length[255]',
			),
			array(
				'field' => 'org',
				'label' => 'lang:us_reg_org',
				'rules' => 'trim',
			),
			array(
				'field' => 'google_id_token',
				'label' => 'lang:us_reg_google_id_token',
				'rules' => 'trim|max_length[2048]',
			)
		),
		'profile' => array (
			array(
				'field' => 'new_email',
				'label' => 'lang:us_reg_email',
				'rules' => 'trim|valid_email|max_length[255]|unique[users.email]|unique[users.new_email]',
			),
			array(
				'field' => 'first_name',
				'label' => 'lang:us_reg_first_name',
				'rules' => 'trim|max_length[255]',
			),
			array(
				'field' => 'last_name',
				'label' => 'lang:us_reg_last_name',
				'rules' => 'trim|max_length[255]',
			),
			array(
				'field' => 'skype',
				'label' => 'lang:us_reg_skype',
				'rules' => 'trim|max_length[255]',
			),
			array(
				'field' => 'google_id_token',
				'label' => 'lang:us_reg_google_id_token',
				'rules' => 'trim|max_length[2048]',
			)
		),
		'change_password' => array(
			array(
				'field' => 'current_password',
				'label' => 'lang:us_current_password',
				'rules' => 'trim|required|max_length[60]',
			),
			array(
				'field' => 'new_password',
				'label' => 'lang:us_new_password',
				'rules' => 'trim|required|max_length[60]',
			),
			array(
				'field' => 'conf_password',
				'label' => 'lang:us_reg_conf_password',
				'rules' => 'trim|required|max_length[60]|matches[new_password]',
			),
		),
		'register' => array(
			array(
				'field' => 'email',
				'label' => 'lang:us_reg_email',
				'rules' => 'trim|required|valid_email|max_length[255]|unique[users.email]',
			)
		)
	);
	protected $insert_validation_rules  = array();
	protected $skip_validation			= true;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}


	public function get_organization_members($organization_id)
	{
		return $this->select('uto.user_id, email, first_name, last_name, avatar, cost_of_time')
					->join('user_to_organizations uto', 'users.user_id = uto.user_id AND enabled = 1 AND organization_id = ' . $organization_id)
					->find_all();
	}

	//--------------------------------------------------------------------------
	// !ACTIVATION
	//--------------------------------------------------------------------------

	/**
	 * Accepts an activation code and validates against a matching entry in the database.
	 *
	 * There are some instances where the activation hash should be removed but
	 * the user should be left inactive (e.g. Admin Activation), so $leave_inactive
	 * enables that use case.
	 *
	 * @param int    $user_id        The user to be activated (null will match any).
	 * @param string $code           The activation code to be verified.
	 * @param bool   $leave_inactive Flag whether to remove the activate hash value,
	 * but leave active = 0.
	 *
	 * @return int User Id on success, false on error.
	 */
	public function activate($user_id, $code, $leave_inactive = false)
	{
		if ($user_id) {
			$this->db->where('user_id', $user_id);
		}

		$query = $this->db->select('user_id')
						  ->where('activate_hash', $code)
						  ->get($this->table_name);

		if ($query->num_rows() !== 1) {
			$this->error = lang('us_err_no_matching_code');
			return false;
		}

		// Now we can find the $user_id, even if it was passed as NULL
		$result = $query->row();
		$user_id = $result->user_id;

		$active = $leave_inactive === false ? 1 : 0;
		if ($this->update($user_id, array('activate_hash' => '', 'active' => $active))) {
			return $user_id;
		}

		return false;
	}

	/**
	 * This function is triggered during account setup to ensure user is not active
	 * and, if not supressed, generate an activation hash code. This function can
	 * be used to deactivate accounts based on public view events.
	 *
	 * @param int    $user_id    The username or email to match to deactivate
	 * @param string $login_type Login Method
	 * @param bool   $make_hash  Create a hash
	 *
	 * @return mixed $activate_hash on success, false on error
	 */
	public function deactivate($user_id, $make_hash = true)
	{
		// create a temp activation code.
		$activate_hash = '';
		if ($make_hash === true) {
			$this->load->helper('string');
			$activate_hash = sha1(random_string('alnum', 40) . time());
		}

		$this->db->update(
			$this->table_name,
			array('active' => 0, 'activate_hash' => $activate_hash),
			array('user_id' => $user_id)
		);

		if ($this->db->affected_rows() != 1) {
			return false;
		}

		return $make_hash ? $activate_hash : true;
	}
	/**
	 * Configure activation for the given user based on current user_activation_method.
	 *
	 * @param number $user_id User's ID.
	 *
	 * @return array A 'message' (string) and 'error' (boolean, true if an error
	 * occurred sending the activation email).
	 */
	public function set_activation($user_id)
	{
		// User activation method
		$activation_method = $this->settings_lib->item('auth.user_activation_method');

		// Prepare user messaging vars
		$emailMsgData   = array();
		$emailView      = '';
		$subject        = '';
		$email_mess     = '';
		$message        = lang('us_email_thank_you');
		$type           = 'success';
		$site_title     = $this->settings_lib->item('site.title');
		$error          = false;
		$ccAdmin      = false;

		switch ($activation_method) {
			case 0:
				// No activation required.
				// Activate the user and send confirmation email.
				$subject = str_replace(
					'[SITE_TITLE]',
					$this->settings_lib->item('site.title'),
					lang('us_account_reg_complete')
				);

				$emailView  = '_emails/activated';
				$message    .= lang('us_account_active_login');

				$emailMsgData = array(
					'title' => $site_title,
					'link'  => site_url(),
				);
				break;
			case 1:
				// Email Activiation.
				// Run the account deactivate to assure everything is set correctly.
				$activation_code    = $this->deactivate($user_id);

				// Create the link to activate membership
				$activate_link = site_url("activate/{$user_id}/{$activation_code}");
				$subject            =  lang('us_email_subj_activate');
				$emailView          = '_emails/activate';
				$message            .= lang('us_check_activate_email');

				$emailMsgData = array(
					'title' => $site_title,
					'code'  => $activation_code,
					'link'  => $activate_link
				);
				break;
			case 2:
				// Admin Activation.
				$ccAdmin   = true;
				$subject    =  lang('us_email_subj_pending');
				$emailView  = '_emails/pending';
				$message    .= lang('us_admin_approval_pending');

				$emailMsgData = array(
					'title' => $site_title,
				);
				break;
		}

		$email_mess = $this->load->view($emailView, $emailMsgData, true);

		// Now send the email
		$this->load->library('emailer/emailer');
		$data = array(
			'to'        => $this->find($user_id)->email,
			'subject'   => $subject,
			'message'   => $email_mess,
		);

		if ($this->emailer->send($data)) {
			// If the message was sent successfully and the admin must be notified
			// (Admin Activation is enabled), send another email to the system_email.
			if ($ccAdmin) {
				/**
				 * @todo Add a setting to allow the user to change the email address
				 * of the recipient of this message.
				 *
				 * @todo Add CC/BCC capabilities to emailer, so this doesn't require
				 * sending a second email.
				 */
				$data['to'] = $this->settings_lib->item('system_email');
				if (! empty($data['to'])) {
					$this->emailer->send($data);
				}
			}
		} else {
			// If the message was not sent successfully, set an error message.
			$message    .= lang('us_err_no_email') . $this->emailer->error;
			$error      = true;
		}

		return array('message' => $message, 'error' => $error);
	}
}
