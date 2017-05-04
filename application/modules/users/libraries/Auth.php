<?php defined('BASEPATH') || exit('No direct script access allowed');

class Auth
{
	/**
	 * @var string The date format used for users.last_login, login_attempts.time,
	 * and user_cookies.created_on. Passed as the first argument of the PHP date()
	 * function when handling any of these values.
	 */
	protected $loginDateFormat = 'Y-m-d H:i:s';

	/** @var string The name of the cookie index holding autologin data. */
	protected $autoLoginIndex = 'autologin';

	/** @var string The separator used in the autologin data. */
	protected $autoLoginSeparator = '~';

	/** @var string The name of the db table containing user tokens. */
	protected $cookiesTable = 'user_cookies';

	/** @var string The name of the db table tracking user login attempts. */
	protected $loginAttemptsTable = 'login_attempts';

	/**
	 * @var bool If true, failed logins will be tracked in activity logs. Controlled
	 * by 'auth.log_failed_login_activity' in application config. Currently, this
	 * only logs occurrences which would increase the login_attempts entry.
	 */
	protected $logFailedLogins = false;

	/** @var boolean Allow use of the "Remember Me" checkbox/cookie. */
	private $allowRemember;

	/** @var object A pointer to the CodeIgniter instance. */
	private $ci;

	/** @var string The ip_address of the current user. */
	private $ip_address;

	/** @var object The logged-in user. */
	private $user;

	//--------------------------------------------------------------------------

	/**
	 * Grab a pointer to the CI instance, get the user's IP address, and attempt
	 * to automatically log in the user.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ip_address = $this->ci->input->ip_address();

		// The users language file is needed for this to work from other modules.
		$this->ci->lang->load('users/users');
		$this->ci->load->model('users/user_model');
		$this->ci->load->library('session');
		$this->ci->load->library('settings/settings_lib');
		$this->ci->load->library('Template');
		$this->ci->load->library('form_validation');

		if ($this->ci->config->item('auth.log_failed_login_activity') === null) {
			$this->ci->config->load('application');
		}
		$this->logFailedLogins = $this->ci->config->item('auth.log_failed_login_activity') ?: false;

		// Try to log the user in from session/cookie data.
		$this->autologin();

		log_message('debug', 'Mem_auth class initialized.');
	}

	/**
	 * Check the session for the required info, then verify it against the database.
	 *
	 * @return boolean True if the user is logged in, else false.
	 */
	public function is_logged_in()
	{
		return (bool) $this->user();
	}

	/**
	 * Attempt to log the user in.
	 *
	 * @param string  $login    The user's login credentials (email).
	 * @param string  $password The user's password.
	 * @param boolean $remember Whether the user should be remembered in the system.
	 *
	 * @return boolean True if the user has authenticated, else false.
	 */
	public function login($login, $password = null, $remember = false, $via_google = false, $google_token = null)
	{
		if ($via_google !== false && (empty($login) || empty($google_token))) {
			Template::set_message(lang('us_fields_required'), 'danger');
			return false;
		}

		if ($via_google === false && (empty($login) || empty($password))) {
			Template::set_message(lang('us_fields_required'), 'danger');
			$this->ci->form_validation->set_rules('login', '', 'required');
			$this->ci->form_validation->set_rules('password', '', 'required');
			$this->ci->form_validation->run();
			return false;
		}

		// Grab the user from the db.
		$selects = array(
			'user_id',
			'email',
			'first_name',
			'last_name',
			'deleted',
			'active',
			'skype',
			'password_hash',
			'force_password_reset',
			'google_id_token'
		);

		$user = $this->ci->user_model->select($selects)->find_by(
				'email',
				$login
			);

		// Check whether the username, email, or password doesn't exist.
		if ($user == false) {
			Template::set_message(lang('us_bad_email_pass'), 'danger');
			return false;
		}

		// Check whether the account has been activated.
		if ($user->active == 0) {
			Template::set_message(lang('us_account_not_active'), 'danger');
			return false;
		}

		// Check whether the account has been soft deleted. The >= 1 check ensures
		// this will still work if the deleted field is a UNIX timestamp.
		if ($user->deleted >= 1) {
			Template::set_message(
				sprintf(
					lang('us_account_deleted'),
					html_escape($this->ci->settings_lib->item('site.system_email'))
				),
				'danger'
			);
			return false;
		}

		if ($via_google === false) {
			// Try password
			if (! $this->check_password($password, $user->password_hash)) {
				// Bad password
				Template::set_message(lang('us_bad_email_pass'), 'danger');
				$this->increase_login_attempts($login, 'us_bad_email_pass');

				return false;
			}

			// Check whether the user needs to reset their password.
			if ($user->force_password_reset == 1) {
				Template::set_message(lang('us_forced_password_reset_note'), 'warning');

				// Generate a reset hash to pass the reset_password checks...
				if (! function_exists('random_string')) {
					$this->ci->load->helper('string');
				}
				$hash = sha1(random_string('alnum', 40) . $user->email);

				// Save the hash to the db so it can be confirmed later.
				$this->ci->user_model->update_where(
					'user_id',
					$user->user_id,
					array('reset_hash' => $hash, 'reset_by' => strtotime("+24 hours"))
				);

				$this->ci->session->set_userdata('pass_check', $hash);
				$this->ci->session->set_userdata('email', $user->email);

				// Redirect the user to the reset password page.
				Template::redirect('users/reset_password');
			}
		}

		$this->clear_login_attempts($login);

		// get user roles base on organization
		$roles = $this->ci->db->select('organization_id, role_id')
									->where('user_id', $user->user_id)
									->where('enabled', 1)
									->get('user_to_organizations')->result();
		
		$user->role_ids = [];
		if (is_array($roles) && count($roles) > 0) {
			foreach ($roles as $role) {
				$user->role_ids[$role->organization_id] = $role->role_id;
			}
		}

		if (empty($google_token)) {
			$google_token = $user->google_id_token;
		}
		
		// The login was successfully validated, so setup the session
		$this->setupSession(
			$user->user_id,
			$user->password_hash,
			$user->email,
			$user->role_ids,
			$remember,
			'',
			$google_token
		);

		// Save the login info
		$this->ci->user_model->update(
			$user->user_id,
			array(
				'last_login' => $this->getLoginTimestamp(),
				'last_ip'	=> $this->ip_address,
			)
		);

		// Clear the cached result of user() (and is_logged_in(), user_id(), etc.).
		// Doesn't fix `$this->current_user` in controller (for this page load)...
		unset($this->user);

		// Can't pass the array directly to trigger, must use a variable.
		$trigger_data = array('user_id' => $user->user_id);
		Events::trigger('after_login', $trigger_data);

		return true;
	}

	/**
	 * Destroy the autologin information and the current session.
	 *
	 * @return void
	 */
	public function logout()
	{
		// Can't pass the array directly to trigger, must use a variable.
		$data = array(
			'user_id' => $this->user_id(),
		);
		Events::trigger('before_logout', $data);

		// Destroy the autologin information
		$this->deleteAutologin();

		// Destroy the session
		$this->ci->session->sess_destroy();
	}

	/**
	 * Check the session for the required info, then verify it against the database.
	 *
	 * @return object/boolean Returns the user info or false.
	 */
	public function user()
	{
		// If the user has already been cached, return it.
		if (isset($this->user)) {
			return $this->user;
		}

		$this->user = false;

		// Is the required session data available?
		if (! $this->ci->session->userdata('identity')
			|| ! $this->ci->session->userdata('user_id')
		) {
			return false;
		}

		// Grab the user account.
		$user = $this->ci->user_model->find($this->ci->session->userdata('user_id'));
		if ($user === false) {
			return false;
		}

		// Ensure user_token is still equivalent to SHA1 of the user_id and password_hash.
		if (sha1($this->ci->session->userdata('user_id') . $user->password_hash . $user->google_id_token)
			!== $this->ci->session->userdata('user_token')
		) {
			return false;
		}

		// get user roles base on organization
		$roles = $this->ci->db->select('organization_id, role_id')
									->where('user_id', $user->user_id)
									->where('enabled', 1)
									->get('user_to_organizations')->result();
		
		$user->role_ids = [];
		if (is_array($roles) && count($roles) > 0) {
			foreach ($roles as $role) {
				$user->role_ids[$role->organization_id] = $role->role_id;
			}
		}

		$user->current_organization_id = null;
		if (isset($this->ci->current_organization_url) && ! is_null($this->ci->current_organization_url)) {
			$org = $this->ci->db->select('o.organization_id')
								->from('organizations o')
								->join('user_to_organizations uo', 'o.organization_id = uo.organization_id', 'left')
								->where('o.url', $this->ci->current_organization_url)
								->where('uo.user_id', $user->user_id)
								->get()->row();
			if (isset($org)) {
				$user->current_organization_id = $org->organization_id;
			}
		}

		$this->user = $user;
		$this->user->user_id = (int) $this->user->user_id;

		return $this->user;
	}

	//--------------------------------------------------------------------------
	// Permissions
	//--------------------------------------------------------------------------

	/**
	 * Verify that the user is logged in and has the appropriate permissions.
	 *
	 * @param string  $permission The permission to check for, e.g. 'Site.Signin.Allow'.
	 * @param integer $role_id    The id of the role to check the permission against.
	 * If role_id is not passed into the method, it assumes the current user's role_id.
	 *
	 * @return boolean True if the user/role has permission or the permission was
	 * not found in the database and $override is true, else false.
	 */
	public function has_permission($permission, $role_id = null, $organization_id = null)
	{
		// Move permission to lowercase for easier checking.
		$permission = strtolower($permission);

		if (empty($role_id) || empty($organization_id)) {
			$user = $this->user();
			if (!$user) {
				return false;
			}
		}

		// if empty $organization_id, get organization id by user session
		if (empty($organization_id)) {
			$organization_id = $user->current_organization_id;
		}

		$this->ci->db->select('count(*) as count')->from('user_to_organizations uto')
					->join('role_to_permissions rtp', 'uto.role_id = rtp.role_id', 'inner')
					->join('permissions p', 'p.permission_id = rtp.permission_id', 'inner');
		// If no role is provided, assume it's for the current logged in user based on organization_id.
		if (empty($role_id)) {
			$role_ids = $user->role_ids;
			if (isset($role_ids[$organization_id])) {
				$role_id = $role_ids[$organization_id];

				$this->ci->db->where('uto.role_id', $role_id);
			} else {
				$user_id = $user->user_id;

				$this->ci->db->where('uto.user_id', $user_id)
							->where('uto.organization_id', $organization_id);
			}
		} else {
			$this->ci->db->where('uto.role_id', $role_id);
		}

		$isset_permissions = $this->ci->db->where('p.key', $permission)
										->where('p.status', 'active')
										->get()->row()->count > 0 ? true : false;

		// Does the user/role have the permission?
		if ($isset_permissions) {
			return true;
		}

		return false;
	}

	/**
     * Check whether a permission is in the system.
     *
     * @param string $permission The case-insensitive name of the permission to check.
     *
     * @return boolean True if the permission was found, else false.
     */
    public function permission_exists($permission)
    {
        // Move permission to lowercase for easier checking.
        $permission = strtolower($permission);

		$permission_exist = $this->ci->db->select('count(*) as count')
									->where('key', $permission)
									->get('permissions')
									->row()->count > 0 ? true : false;
		
		return $permission_exist;
    }

	/**
	 * Check whether a user is logged in (and, optionally of the correct role) and,
	 * if not, send them to the login screen.
	 *
	 * If no permission is checked, will simply verify that the user is logged in.
	 * If a permission is passed in to the first parameter, it will check the user's
	 * role and verify that role has the appropriate permission.
	 *
	 * @param string $permission (Optional) The permission to check for.
	 * @param string $uri        (Optional) The redirect URI if the user does not
	 * have the correct permission.
	 *
	 * @return boolean True if the user has the appropriate access permissions.
	 * Redirect to the previous page if the user doesn't have permissions.
	 * Redirect to LOGIN_AREA page if the user is not logged in.
	 */
	public function restrict($permission = null, $uri = null)
	{
		// If user isn't logged in, redirect to the login page.
		if ($this->is_logged_in() === false) {
			Template::set_message($this->ci->lang->line('us_must_login'), 'danger');
			Template::redirect(LOGIN_URL);
		}

		// Check whether the user has the proper permissions.
		if (empty($permission) || $this->has_permission($permission)) {
			return true;
		}

		// If the user is logged in, but does not have permission...

		// If $uri is not set, get the previous page from the session.
		if (! $uri) {
			$uri = $this->ci->session->userdata('previous_page');

			// If previous page and current page are the same, but the user no longer
			// has permission, redirect to site URL to prevent an infinite loop.
			if ($uri == current_url()) {
				$uri = site_url();
			}
		}

		// Inform the user of the lack of permission and redirect.
		Template::set_message(lang('us_no_permission'), 'attention');
		Template::redirect($uri);
	}

	//--------------------------------------------------------------------------
	// Password Methods
	//--------------------------------------------------------------------------

	/**
	 * Check the supplied password against the supplied hash.
	 *
	 * @param string $password The password to check.
	 * @param string $hash     The hash.
	 *
	 * @return boolean True if the password and hash match, else false.
	 */
	public function check_password($password, $hash)
	{
		// Load the password hash library
		$hasher = $this->getPasswordHasher(-1);

		// Try password
		return $hasher->CheckPassword($password, $hash);
	}

	/**
	 * Hash a password.
	 *
	 * @param string $pass        The password to hash
	 * @param integer $iterations The number of iterations used in hashing the password.
	 *
	 * @return array An associative array containing the hashed password and number
	 * of iterations.
	 */
	public function hash_password($pass, $iterations = 0)
	{
		// The shortest valid hash phpass can currently return is 20 characters,
		// which would only happen with CRYPT_EXT_DES.
		$minHashLength = 20;

		// If $iterations wasn't passed, get it from the settings.
		if (empty($iterations)
			|| ! is_numeric($iterations)
			|| $iterations <= 0
		) {
			$iterations = $this->ci->settings_lib->item('password_iterations');
		}

		// Load the password hash library and hash the password.
		$hasher   = $this->getPasswordHasher($iterations);
		$password = $hasher->HashPassword($pass);
		unset($hasher);

		// If the password is shorter than the minimum hash length, something failed.
		if (strlen($password) < $minHashLength) {
			return false;
		}

		return array('hash' => $password, 'iterations' => $iterations);
	}

	/**
	 * Loads the PasswordHash library as needed and returns a new instance.
	 *
	 * Note: Moving the loading of the 'password_iterations' setting into this method
	 * was considered. Since the $iterations value is only really needed for hashing
	 * a password, and the 'password_iterations' value is only used when the $iterations
	 * value passed to the hash_password() method is not a positive integer, it
	 * made more sense to leave it there than to add some indicator to this method
	 * that the calling method didn't need to retrieve the 'password_iterations'
	 * value.
	 *
	 * @param integer $iterations The number of iterations to be used in hashing
	 * passwords.
	 *
	 * @return PasswordHash The password hasher.
	 */
	protected function getPasswordHasher($iterations)
	{
		if (! class_exists('PasswordHash', false)) {
			require(dirname(__FILE__) . '/../libraries/PasswordHash.php');
		}

		return new PasswordHash((int) $iterations, false);
	}

	//--------------------------------------------------------------------
	// !LOGIN ATTEMPTS
	//--------------------------------------------------------------------

	/**
	 * Get number of login attempts from the given IP-address and/or login.
	 *
	 * @param string $login (Optional) The login id to check for (email).
	 * If no login is passed in, it will only check against the IP Address of the
	 * current user.
	 *
	 * @return integer The number of attempts.
	 */
	public function num_login_attempts($login = null)
	{
		$this->ci->db->select('1', false)
					 ->where('ip_address', $this->ip_address);

		if (strlen($login) > 0) {
			$this->ci->db->or_where('login', $login);
		}

		$query = $this->ci->db->get($this->loginAttemptsTable);

		return $query->num_rows();
	}

	/**
	 * Clear all login attempts for this user, as well as expired logins.
	 *
	 * @param string  $login   The login credentials (typically email).
	 * @param integer $expires The expiration time (in seconds). Attempts older
	 * than this value will be deleted.
	 *
	 * @return void
	 */
	protected function clear_login_attempts($login, $expires = 86400)
	{
		$this->ci->db->where(array('ip_address' => $this->ip_address, 'login' => $login))
					 ->or_where('time <', $this->getLoginTimestamp(time() - $expires))
					 ->delete($this->loginAttemptsTable);
	}

	/**
	 * Record a login attempt in the database.
	 *
	 * @param string $login The login id used (typically email).
	 * @param string $reason The key to a language line indicating why access was
	 * denied.
	 *
	 * @return void
	 */
	protected function increase_login_attempts($login, $reason = '')
	{
		if ($this->logFailedLogins) {
			if (! class_exists('activity_model', false)) {
				$this->ci->load->model('activities/activity_model');
			}
			$this->ci->activity_model->log_activity(
				0,
				sprintf(
					lang('users_act_invalid_login_attempt'),
					$this->ip_address,
					$login,
					empty($reason) ? '' : lang($reason)
				),
				'users'
			);
		}
		$this->ci->db->insert(
			$this->loginAttemptsTable,
			array(
				'ip_address' => $this->ip_address,
				'login'	  => $login,
				'time'	   => $this->getLoginTimestamp(),
			)
		);
	}

	//--------------------------------------------------------------------------
	// !UTILITY METHODS
	//--------------------------------------------------------------------------

	/**
	 * Retrieve the logged identity from the current session. Built from the user's
	 * submitted login.
	 *
	 * @return string/boolean The identity used to login, or false.
	 */
	public function identity()
	{
		if (! $this->is_logged_in()) {
			return false;
		}
		return $this->ci->session->userdata('identity');
	}

	/**
	 * Retrieve the user_id from the current session.
	 *
	 * @return integer/boolean The user's ID or false.
	 */
	public function user_id()
	{
		if (! $this->is_logged_in()) {
			return false;
		}
		return $this->user()->user_id;
	}

	/**
	 * Gets a timestamp using $this->loginDateFormat and the system's configured
	 * 'time_reference'.
	 *
	 * @param integer $time A UNIX timestamp.
	 *
	 * @return string A timestamp formatted according to $this->loginDateFormat.
	 */
	protected function getLoginTimestamp($time = null)
	{
		if (empty($time)) {
			$time = time();
		}
		return strtolower($this->ci->config->item('time_reference')) == 'gmt' ?
			gmdate($this->loginDateFormat, $time) : date($this->loginDateFormat, $time);
	}

	//--------------------------------------------------------------------------
	// Private Methods
	//--------------------------------------------------------------------------

	/**
	 * Create the session information for the current user and create an
	 * autologin cookie if required.
	 *
	 * @param integer $userId   An int with the user's id.
	 * @param string  $email The user's email.
	 * @param string  $hash     The user's password hash. Used to create a new,
	 * unique user token.
	 * @param string  $email    The user's email address.
	 * @param integer $roleId   The user's role_id.
	 * @param boolean $remember Whether to keep the user logged in.
	 * @param string  $oldToken User's db token to test against.
	 *
	 * @return boolean True/false on success/failure.
	 */
	private function setupSession($userId, $hash, $email, $roleIds, $remember = false, $oldToken = null, $google_token = null)
	{
		$login = $email;

		// Save the user's session info.
		$this->ci->session->set_userdata(
			array(
				'user_id'	 => $userId,
				'auth_custom' => $login,
				'user_token'  => sha1($userId . $hash . $google_token),
				'identity'	=> $login,
				'role_ids'	 => $roleIds,
				'logged_in'   => true,
			)
		);

		// Should we remember the user?
		if ($remember === true) {
			return $this->createAutologin($userId, $oldToken);
		}

		return true;
	}

	//--------------------------------------------------------------------------
	// !AUTO-LOGIN
	//--------------------------------------------------------------------------

	/**
	 * Attempt to log the user in based on an existing 'autologin' cookie.
	 *
	 * @return void
	 */
	private function autologin()
	{
		if (! $this->allowRemember()) {
			return;
		}

		$cookie = $this->getAutologinCookie(true);
		if (is_null($cookie)) {
			return;
		}

		// Save logged in status to reduce db access.
		$this->logged_in = true;

		// If the session exists, there's nothing more to do.
		if ($this->ci->session->userdata('user_id')) {
			return;
		}

		// Grab the current user info for the session.
		$this->ci->load->model('users/user_model');
		$user = $this->ci->user_model->select(array('user_id', 'email', 'password_hash', 'google_id_token'))
									 ->find($cookie->userId);

		// If no user was found, the session can't be created properly.
		if (! $user) {
			return;
		}

		// get user roles base on organization
		$roles = $this->ci->db->select('organization_id, role_id')
									->where('user_id', $user->user_id)
									->where('enabled', 1)
									->get('user_to_organizations')->result();
		
		$user->role_ids = [];
		if (is_array($roles) && count($roles) > 0) {
			foreach ($roles as $role) {
				$user->role_ids[$role->organization_id] = $role->role_id;
			}
		}

		$this->setupSession(
			$user->user_id,
			$user->password_hash,
			$user->email,
			$user->role_ids,
			true,
			$cookie->token,
			$user->google_id_token
		);
	}

	/**
	 * Create the auto-login entry in the database. This method uses Charles Miller's
	 * thoughts at:
	 * http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice/
	 *
	 * @param integer $user_id   An int representing the user_id.
	 * @param string  $oldToken The previous token that was used to login with.
	 *
	 * @return boolean Whether the autologin was created or not.
	 */
	private function createAutologin($user_id, $oldToken = null)
	{
		if (! $this->allowRemember()) {
			return false;
		}

		return $this->setAutologinCookie($user_id, $oldToken);
	}

	/**
	 * Delete the autologin cookie for the current user.
	 *
	 * @return void
	 */
	private function deleteAutologin()
	{
		if (! $this->allowRemember()) {
			return;
		}

		$this->deleteAutologinCookie();
		$this->cleanupCookies();
	}

	//--------------------------------------------------------------------------
	// Cookie Methods
	//--------------------------------------------------------------------------

	/**
	 * Remove old user tokens from the database.
	 *
	 * @return void
	 */
	private function cleanupCookies()
	{
		// Perform a clean up of any autologins older than 2 months.
		$this->ci->db->where('created_on <', $this->getLoginTimestamp(strtotime('2 months ago')))
					 ->delete($this->cookiesTable);
	}

	/**
	 * Delete the autologin data from the user's cookie.
	 *
	 * @param  boolean $removeDbEntry Remove the user's token from the database.
	 *
	 * @return void
	 */
	private function deleteAutologinCookie($removeDbEntry = true)
	{
		if ($removeDbEntry) {
			// Grab the cookie to determine which row in the table to delete.
			$cookie = $this->getAutologinCookie(false, false);
			if (! is_null($cookie)) {
				// Now delete the cookie from the database.
				$this->ci->db->where('user_id', $cookie->userId)
							 ->where('token', $cookie->token)
							 ->delete($this->cookiesTable);
			}
		}

		if (! function_exists('delete_cookie')) {
			$this->ci->load->helper('cookie');
		}
		delete_cookie($this->autoLoginIndex);
	}

	/**
	 * Get the user ID and token from the cookie.
	 *
	 * @param  boolean $xssClean Pass the data through CI's xss_clean().
	 * @param  boolean $checkDb  Verify that the cookie data matches the database.
	 *
	 * @return object The login cookie (with userId and token properties) or null
	 * if the cookie was not found or did not match the database (when $checkDb
	 * is true).
	 */
	private function getAutologinCookie($xssClean = null, $checkDb = true)
	{
		if (! function_exists('get_cookie')) {
			$this->ci->load->helper('cookie');
		}

		$cookie = get_cookie($this->autoLoginIndex, $xssClean);
		if (! $cookie) {
			return null;
		}

		$loginCookie = new stdClass;
		list($loginCookie->userId, $loginCookie->token) = explode($this->autoLoginSeparator, $cookie);

		if ($checkDb) {
			// Try to pull a match for the cookie from the database.
			$query = $this->ci->db->where(array('user_id' => $loginCookie->userId, 'token' => $loginCookie->token))
								  ->get($this->cookiesTable);
			if ($query->num_rows() != 1) {
				return null;
			}
		}

		return $loginCookie;
	}

	/**
	 * Set the autologin cookie data.
	 * @param integer $userId   The user's ID.
	 * @param string  $oldToken The user's existing autologin token.
	 *
	 * @return boolean True if the data was set successfully in the cookie and database,
	 * else false.
	 */
	private function setAutologinCookie($userId, $oldToken = null)
	{
		// Generate a random string for the token.
		if (! function_exists('random_string')) {
			$this->ci->load->helper('string');
		}
		$token = random_string('alnum', 128);

		if (empty($oldToken)) {
			$this->ci->db->insert(
				$this->cookiesTable,
				array(
					'user_id'	=> $userId,
					'token'	  => $token,
					'created_on' => $this->getLoginTimestamp(),
				)
			);
		} else {
			$this->ci->db->where('user_id', $userId)
						 ->where('token', $oldToken)
						 ->set('token', $token)
						 ->set('created_on', $this->getLoginTimestamp())
						 ->update($this->cookiesTable);
		}

		if ($this->ci->db->affected_rows()) {
			if (! function_exists('set_cookie')) {
				$this->ci->load->helper('cookie');
			}

			set_cookie(
				$this->autoLoginIndex,
				"{$userId}{$this->autoLoginSeparator}{$token}",
				$this->ci->settings_lib->item('auth.remember_length')
			);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------------
	// Utility Methods
	//--------------------------------------------------------------------------

	/**
	 * Retrieve the 'auth.allow_remember' setting from the settings library and
	 * store it for the library's internal use.
	 *
	 * @return boolean True if the "Remember Me" checkbox is permitted by the site's
	 * settings to be displayed on the login form, else false.
	 */
	private function allowRemember()
	{
		if (isset($this->allowRemember)) {
			return $this->allowRemember;
		}

		$this->allowRemember = (bool) $this->ci->settings_lib->item('auth.allow_remember');
		return $this->allowRemember;
	}
}

//------------------------------------------------------------------------------
// Helper Functions
//------------------------------------------------------------------------------

if (! function_exists('has_permission')) {
    /**
     * A convenient shorthand for checking user permissions.
     *
     * @param string  $permission The permission to check for, ie 'Site.Signin.Allow'.
     *
     * @return boolean True if the user has the permission or $override is true
     * and the permission wasn't found in the system, else false.
     */
    function has_permission($permission, $role_id = null, $organization_id = null)
    {
        return get_instance()->auth->has_permission($permission, $role_id, $organization_id);
    }
}

if (! function_exists('permission_exists')) {
    /**
     * Check to see whether a permission is in the system.
     *
     * @param string $permission Case-insensitive permission to check.
     *
     * @return boolean True if the permission exists, else false.
     */
    function permission_exists($permission)
    {
        return get_instance()->auth->permission_exists($permission);
    }
}
