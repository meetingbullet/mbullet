<?php defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Bonfire
 *
 * An open source project to allow developers to jumpstart their development of
 * CodeIgniter applications.
 *
 * @package   Bonfire
 * @author    Bonfire Dev Team
 * @copyright Copyright (c) 2011 - 2014, Bonfire Dev Team
 * @license   http://opensource.org/licenses/MIT
 * @link      http://cibonfire.com
 * @since     Version 1.0
 * @filesource
 */

/**
 * Users Controller.
 *
 * Provides front-end functions for users, including access to login and logout.
 *
 * @package Bonfire\Modules\Users\Controllers\Users
 * @author     Bonfire Dev Team
 * @link    http://cibonfire.com/docs/developer
 */
class Users extends Front_Controller
{
	/** @var array Site's settings to be passed to the view. */
	private $siteSettings;

	/**
	 * Setup the required libraries etc.
	 *
	 * @retun void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->helper('form');
		$this->load->library('form_validation');

		$this->load->model('users/user_model');

		$this->load->library('users/auth');

		$this->lang->load('users');
		$this->siteSettings = $this->settings_lib->find_all();
		if ($this->siteSettings['auth.password_show_labels'] == 1) {
			Assets::add_module_js('users', 'password_strength.js');
			Assets::add_module_js('users', 'jquery.strength.js');
		}
		//load google api config file
		$this->config->load('users/google_api');
		// Set up login using google account
		Assets::add_module_js('users', 'google_api.js');
		Assets::add_module_js('users', 'users.js');
		Template::set('use_google_api', true);
		Template::set('client_id', $this->config->item('client_id'));
	}

	// -------------------------------------------------------------------------
	// Authentication (Login/Logout)
	// -------------------------------------------------------------------------

	/**
	 * Present the login view and allow the user to login.
	 *
	 * @return void
	 */
	public function login()
	{
		// If the user is already logged in, go home.
		if ($this->auth->is_logged_in() !== false) {
			Template::redirect('/');
		}
		// include google client api
		require_once APPPATH . 'modules/users/libraries/google-api-client/vendor/autoload.php';
		$client_id = $this->config->item('client_id');
		$client_secret = $this->config->item('client_secret');
		$redirect_uri = $this->config->item('redirect_uri');

		$client = new Google_Client();
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->addScope("email");
		$client->addScope("profile");

		Template::set('auth_url', $client->createAuthUrl());
		// if logged in via google
		$service = new Google_Service_Oauth2($client);

		if (isset($_GET['error'])) {
			Template::set_message(lang('us_failed_login_attempts'), 'danger');
		}

		if (isset($_GET['code'])) {
			try {
				$client->authenticate($_GET['code']);
				$access_token = $client->getAccessToken();
				$google_user = $service->userinfo->get();

				$user = $this->user_model->find_by('email', $google_user->email);
				if (! $user) {
					$added = $this->user_model->insert([
						'email' => $google_user->email,
						'google_id_token' => $access_token['id_token'],
						'first_name' => $google_user->given_name,
						'last_name' => $google_user->family_name,
						'avatar' => $google_user->picture,
						'active' => 1
					]);

					if (! $added) {
						throw new Exception(lang('us_failed_login_attempts'));
					}
				} else {
					$updated = $this->user_model->update($user->user_id, [
						'google_id_token' => $access_token['id_token'],
						'first_name' => $google_user->given_name,
						'last_name' => $google_user->family_name,
						'avatar' => $google_user->picture,
						'active' => 1
					]);

					if (! $updated) {
						throw new Exception(lang('us_failed_login_attempts'));
					}
				}
			} catch (Exception $e) {
				$google_login_error = true;
				Template::set_message($e->getMessage(), 'danger');
			}
		}
		// Try to login.
		if ((isset($_POST['log-me-in'])
			&& true === $this->auth->login(
				$this->input->post('login'),
				$this->input->post('password'),
				$this->input->post('remember_me') == '1'
			)) || (isset($_GET['code'])
			&& !isset($google_login_error)
			&& true === $this->auth->login(
				$google_user->email,
				null,
				false,
				true,
				$access_token['id_token']
			))
		) {
			log_activity(
				$this->auth->user_id(),
				lang('us_log_logged') . ': ' . $this->input->ip_address(),
				'users'
			);

			// // Now redirect. (If this ever changes to render something, note that
			// // auth->login() currently doesn't attempt to fix `$this->current_user`
			// // for the current page load).

			// // If the site is configured to use role-based login destinations and
			// // the login destination has been set...
			// if ($this->settings_lib->item('auth.do_login_redirect')
			// 	&& ! empty($this->auth->login_destination)
			// ) {
			// 	Template::redirect($this->auth->login_destination);
			// }

			// // If possible, send the user to the requested page.
			// if (! empty($this->requested_page)) {
			// 	Template::redirect($this->requested_page);
			// }

			// // If there is nowhere else to go, go home.
			// Template::redirect('/');
			$this->check_current_email();
			redirect('/');
		}

		Assets::add_css('font-awesome/css/font-awesome.min.css');
		Assets::add_module_js('users', 'users.js');
		Template::set('page_title', 'Login');
		Template::render('account');
	}

	/**
	 * Present the login via google account.
	 *
	 * @return void
	 */
	private function login_via_google($google_data = null)
	{
		if (! $google_data['gg_token']) {
			echo json_encode([
				'status' => 'fail',
				'reason' => 1
			]);
			exit;
		}

		$token = $google_data['gg_token'];
		if (empty($this->config->item('tokeninfo_endpoint'))) {
			$this->config->load('google_api');
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config->item('tokeninfo_endpoint') . '?id_token=' . $token);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($result);
		if (isset($result->error_description)) {
			echo json_encode([
				'status' => 'fail',
				'reason' => 2
			]);
			exit;
		}

		$email = $result->email;

		$user = $this->user_model->find_by('email', $email);
		if (! $user) {
			$added = $this->user_model->insert([
				'email' => $email,
				'google_id_token' => $token
			]);

			if (! $add) {
				echo json_encode([
					'status' => 'fail',
					'reason' => 3
				]);
				exit;
			}
		} else {
			$updated = $this->user_model->update($user->user_id, [
				'google_id_token' => $token
			]);

			if (! $updated) {
				echo json_encode([
					'status' => 'fail',
					'reason' => 4
				]);
				exit;
			}
		}

		if (! class_exists('Auth')) {
			$this->load->library('users/Auth');
		}

		$logged = $this->auth->login($email, null, false, true, $token);
		if (! $logged) {
			echo json_encode([
				'status' => 'fail',
				'reason' => 5
			]);
			exit;
		}

		echo json_encode([
			'status' => 'success',
			'redirect' => base_url()
		]);
		exit;
	}

	/**
	 * Log out, destroy the session, and cleanup, then redirect to the home page.
	 *
	 * @return void
	 */
	public function logout()
	{
		if (isset($this->current_user->user_id)) {
			// Login session is valid. Log the Activity.
			log_activity(
				$this->current_user->user_id,
				lang('us_log_logged_out') . ': ' . $this->input->ip_address(),
				'users'
			);
		}

		// Always clear browser data (don't silently ignore user requests).
		$this->auth->logout();
		Template::redirect('/');
	}

	// -------------------------------------------------------------------------
	// User Management (Register/Update Profile)
	// -------------------------------------------------------------------------

	/**
	 * Allow a user to edit their own profile information.
	 *
	 * @return void
	 */
	public function profile()
	{
		// Make sure the user is logged in.
		$this->auth->restrict();
		$this->set_current_user();

		$this->load->helper('date');

		$this->load->config('address');
		$this->load->helper('address');

		if (isset($_POST['save'])) {
			$user_id = $this->current_user->user_id;
			$user = $this->user_model->find($user_id);
			$rules = $this->user_model->get_validation_rules();
			$this->form_validation->set_rules($rules['profile']);

			if ($this->form_validation->run() !== false) {
				$data = $this->user_model->prep_data($this->input->post());

				if ($data['avatar']['size'] > 0) {
					if ($user->avatar) {
						@unlink($upload_config['upload_path'] . $user->avatar);
					}

					$upload_config = $this->config->load('upload');
					$this->load->library('upload', $upload_config);
					$this->upload->do_upload('avatar');
					$data['avatar'] = $this->upload->data();
				} else {
					unset($data['avatar']);
				}

				// User cannot change his email
				unset($data['email']);

				if (isset($data['password'])) {
					$data['password'] = $this->auth->hash_password($data['password']);
				}

				$updated = $this->user_model->update($user_id, $data);

				if (! $updated) {
					Template::set_message(lang('us_profile_updated_error'), 'danger');
				} else {
					Template::set_message(lang('us_profile_updated_success'), 'success');
				}
			} else {
				Template::set_message(validation_errors(), 'danger');
			}
		}

		// Get the current user information.
		$user = $this->user_model->find($this->current_user->user_id);

		Template::set('user', $user);
		Template::set('languages', unserialize($this->settings_lib->item('site.languages')));
		Template::set_view('profile');
		Template::render('account');
	}

	/**
	 * Display the registration form for the user and manage the registration process.
	 *
	 * The redirect URLs for success (Login) and failure (register) can be overridden
	 * by including a hidden field in the form for each, named 'register_url' and
	 * 'login_url' respectively.
	 *
	 * @return void
	 */
	public function register()
	{
		// Are users allowed to register?
		if (! $this->settings_lib->item('auth.allow_register')) {
			Template::set_message(lang('us_register_disabled'), 'danger');
			redirect('/');
		}

		if ($this->auth->is_logged_in() === true) {
			redirect('/');
		}

		if ($this->input->post()) {
			$rules = $this->user_model->get_validation_rules();
			$this->form_validation->set_rules($rules['register']);

			if ($this->form_validation->run() !== false) {
				redirect('/users/create_profile?email=' . $this->input->post('email'));
			} else {
				Template::set_message(validation_errors(), 'danger');
			}
		}

		Template::set('languages', unserialize($this->settings_lib->item('site.languages')));
		Template::set('page_title', 'Register');
		Template::render('account');
	}
	/**
	 * Display the create profile form for the user and manage the registration process.
	 *
	 *
	 * @return void
	 */
	public function create_profile()
	{
		// Are users allowed to register?
		if (! $this->settings_lib->item('auth.allow_register')) {
			Template::set_message(lang('us_register_disabled'), 'danger');
			Template::redirect('/');
		}

		if ($this->auth->is_logged_in() === true) {
			Template::redirect('/');
		}

		$upload_config = $this->config->load('upload');
		$this->load->library('upload', $upload_config);

		if ($this->input->post()) {
			$rules = $this->user_model->get_validation_rules();
			$this->form_validation->set_rules($rules['create_profile']);

			if ($this->form_validation->run() !== false) {
				$post_avatar = $this->input->post('avatar');
				$avatar = NULL;

				if ($post_avatar['size'] > 0) {
					$this->upload->do_upload('avatar');
					$avatar = $this->upload->data();
				}

				$password = $this->auth->hash_password($this->input->post('password'));
				if (empty($password) || empty($password['hash'])) {
					Template::set_message(lang('us_register_failed'), 'danger');
					@unlink($upload_config['upload_path'] . $data['avatar']);
				} else {
					$data = [
						'avatar' => $avatar['file_name'],
						'first_name' => $this->input->post('first_name'),
						'last_name' => $this->input->post('last_name'),
						'email' => $this->input->post('email'),
						'skype' => $this->input->post('skype'),
						'password_hash' => $password['hash'],
					];

					$added = $this->user_model->insert($data);
					if (! $added) {
						Template::set_message(lang('us_register_failed'), 'danger');
						@unlink($upload_config['upload_path'] . $data['avatar']);
					} else {
						$user_id = $added;
						$activation = $this->user_model->set_activation($user_id);

						$message = $activation['message'];
						$error = $activation['error'];

						log_activity($user_id, lang('us_log_register'), 'users');
						Template::set_message($message, $error === true ? 'danger' : 'success');
						redirect(LOGIN_URL);
					}
				}
			} else {
				if (form_error('email') != '') {
					Template::set_message(form_error('email'), 'danger');
					redirect(REGISTER_URL);
					// Template::redirect('/users/create_profile?email=' . $this->input->post('email'));
				}
				Template::set_message(validation_errors(), 'danger');
			}
		}

		Template::render('account');
	}
	// public function register()
	// {
	// 	// Are users allowed to register?
	// 	if (! $this->settings_lib->item('auth.allow_register')) {
	// 		Template::set_message(lang('us_register_disabled'), 'danger');
	// 		Template::redirect('/');
	// 	}

	// 	$register_url = $this->input->post('register_url') ?: REGISTER_URL;
	// 	$login_url	= $this->input->post('login_url') ?: LOGIN_URL;

	// 	$this->load->model('roles/role_model');
	// 	$this->load->helper('date');

	// 	$this->load->config('address');
	// 	$this->load->helper('address');

	// 	$this->load->config('user_meta');
	// 	$meta_fields = config_item('user_meta_fields');
	// 	Template::set('meta_fields', $meta_fields);

	// 	if (isset($_POST['register'])) {
	// 		if ($userId = $this->saveUser('insert', 0, $meta_fields)) {
	// 			// User Activation
	// 			$activation = $this->user_model->set_activation($userId);
	// 			$message = $activation['message'];
	// 			$error   = $activation['danger'];

	// 			Template::set_message($message, $error ? 'danger' : 'success');

	// 			log_activity($userId, lang('us_log_register'), 'users');
	// 			Template::redirect($login_url);
	// 		}

	// 		Template::set_message(lang('us_registration_fail'), 'danger');
	// 		// Don't redirect because validation errors will be lost.
	// 	}

	// 	if ($this->siteSettings['auth.password_show_labels'] == 1) {
	// 		Assets::add_js(
	// 			$this->load->view('users_js', array('settings' => $this->siteSettings), true),
	// 			'inline'
	// 		);
	// 	}

	// 	// // Generate password hint messages.
	// 	// $this->user_model->password_hints();

	// 	Template::set_view('users/register');
	// 	Template::set('languages', unserialize($this->settings_lib->item('site.languages')));
	// 	Template::set('page_title', 'Register');
	// 	Template::render();
	// }

	// -------------------------------------------------------------------------
	// Password Management
	// -------------------------------------------------------------------------

	/**
	 * Allow a user to request the reset of a forgotten password. An email is sent
	 * with a special temporary link that is only valid for 24 hours. This link
	 * takes the user to reset_password().
	 *
	 * @return void
	 */
	public function forgot_password()
	{
		// If the user is logged in, go home.
		if ($this->auth->is_logged_in() !== false) {
			Template::redirect('/');
		}

		if (isset($_POST['send'])) {
			// Validate the form to ensure a valid email was entered.
			$this->form_validation->set_rules('email', 'lang:bf_email', 'required|trim|valid_email');
			if ($this->form_validation->run() !== false) {
				// Validation passed. Does the user actually exist?
				$user = $this->user_model->find_by('email', $this->input->post('email'));
				if ($user === false) {
					// No user found with the entered email address.
					Template::set_message(lang('us_invalid_email'), 'danger');
				} else {
					// User exists, create a hash to confirm the reset request.
					$this->load->helper('string');
					$hash = sha1(random_string('alnum', 40) . $this->input->post('email'));

					// Save the hash to the db for later retrieval.
					$this->user_model->update_where(
						'email',
						$this->input->post('email'),
						array('reset_hash' => $hash, 'reset_by' => strtotime("+24 hours"))
					);

					// Create the link to reset the password.
					$pass_link = site_url('reset_password/' . str_replace('@', ':', $this->input->post('email')) . "/{$hash}");

					// Now send the email
					$this->load->library('emailer/emailer');
					$data = array(
						'to'	  => $this->input->post('email'),
						'subject' => lang('us_reset_pass_subject'),
						'message' => $this->load->view(
							'_emails/forgot_password',
							array('link' => $pass_link),
							true
						),
					);

					if ($this->emailer->send($data)) {
						Template::set_message(lang('us_reset_pass_message'), 'success');
					} else {
						Template::set_message(lang('us_reset_pass_error') . $this->emailer->error, 'danger');
					}
				}
			} else {
				Template::set_message(validation_errors(), 'danger');
			}
		}

		Template::set_view('users/forgot_password');
		Template::set('page_title', 'Password Reset');
		Template::render('account');
	}

	/**
	 * Allows the user to create a new password for their account. At the moment,
	 * the only way to get here is to go through the forgot_password() process,
	 * which creates a unique code that is only valid for 24 hours.
	 *
	 * Since 0.7 this method is also reached via the force_password_reset security
	 * features.
	 *
	 * @param string $email The email address to check against.
	 * @param string $code  A randomly generated alphanumeric code. (Generated by
	 * forgot_password()).
	 *
	 * @return void
	 */
	public function reset_password($email = '', $code = '')
	{
		// If the user is logged in, go home.
		if ($this->auth->is_logged_in() !== false) {
			Template::redirect('/');
		}

		// Bonfire may have stored the email and code in the session.
		if (empty($code) && $this->session->userdata('pass_check')) {
			$code = $this->session->userdata('pass_check');
		}

		if (empty($email) && $this->session->userdata('email')) {
			$email = $this->session->userdata('email');
		}

		// If there is no code/email, then it's not a valid request.
		if (empty($code) || empty($email)) {
			Template::set_message(lang('us_reset_invalid_email'), 'danger');
			Template::redirect(LOGIN_URL);
		}

			// Handle the form
		if (isset($_POST['set_password'])) {
			$this->form_validation->set_rules('password', 'lang:bf_password', 'required|max_length[120]|valid_password');
			$this->form_validation->set_rules('pass_confirm', 'lang:bf_password_confirm', 'required|matches[password]');

			if ($this->form_validation->run() !== false) {
				// The user model will create the password hash.
				$data = array(
					'password' => $this->input->post('password'),
					'reset_by' => 0,
					'reset_hash' => '',
					'force_password_reset' => 0,
				);

				if ($this->user_model->update($this->input->post('user_id'), $data)) {
					log_activity($this->input->post('user_id'), lang('us_log_reset'), 'users');

					Template::set_message(lang('us_reset_password_success'), 'success');
					Template::redirect(LOGIN_URL);
				}

				if (! empty($this->user_model->error)) {
					Template::set_message(sprintf(lang('us_reset_password_error'), $this->user_model->error), 'danger');
				}
			} else {
				Template::set_message(validation_errors(), 'danger');
			}
		}

		// Check the code against the database
		$email = str_replace(':', '@', $email);
		$user = $this->user_model->find_by(
			array(
				'email'	   => $email,
				'reset_hash'  => $code,
				'reset_by >=' => time(),
			)
		);

		// $user will be an Object if a single result was returned.
		if (! is_object($user)) {
			Template::set_message(lang('us_reset_invalid_email'), 'danger');
			Template::redirect(LOGIN_URL);
		}

		if ($this->siteSettings['auth.password_show_labels'] == 1) {
			Assets::add_js(
				$this->load->view('users_js', array('settings' => $this->siteSettings), true),
				'inline'
			);
		}

		// At this point, it is a valid request....
		Template::set('user', $user);

		Template::set_view('users/reset_password');
		Template::render('account');
	}

	//--------------------------------------------------------------------------
	// ACTIVATION METHODS
	//--------------------------------------------------------------------------

	/**
	 * Activate user.
	 *
	 * Checks a passed activation code and, if verified, enables the user account.
	 * If the code fails, an error is generated.
	 *
	 * @param  integer $user_id The user's ID.
	 *
	 * @return void
	 */
	public function activate($user_id = null, $code = null)
	{
		$activated = $this->user_model->activate($user_id, $code);
		if (! $activated) {
			Template::set_message($this->user_model->error . '. ' . lang('us_err_activate_code'), 'danger');

			if (isset($_POST['activate'])) {
				$this->form_validation->set_rules('code', 'Verification Code', 'required|trim');
				if ($this->form_validation->run()) {
					$code = $this->input->post('code');
					$activated = $this->user_model->activate($user_id, $code);
					if ($activated) {
						$user_id = $activated;

						// Now send the email.
						$this->load->library('emailer/emailer');
						$email_message_data = array(
							'title' => $this->settings_lib->item('site.title'),
							'link'  => site_url(LOGIN_URL),
						);
						$data = array(
							'to'	  => $this->user_model->find($user_id)->email,
							'subject' => lang('us_account_active'),
							'message' => $this->load->view('_emails/activated', $email_message_data, true),
						);

						if ($this->emailer->send($data)) {
							Template::set_message(lang('us_account_active'), 'success');
						} else {
							Template::set_message(lang('us_err_no_email'). $this->emailer->error, 'danger');
						}

						// Template::redirect('/');
					} else {
						if (! empty($this->user_model->error)) {
							Template::set_message($this->user_model->error . '. ' . lang('us_err_activate_code'), 'danger');
						}
					}
				} else {
					Template::set_message(validation_errors(), 'danger');
				}
			}
		} else {
			Template::set_message(lang('us_account_active'), 'success');
			redirect('/login');
		}
		Template::render('account');
	}

	/**
	 * Allow a user to request another activation code. If the email address matches
	 * an existing account, the code is resent.
	 *
	 * @return void
	 */
	public function resend_activation()
	{
		if (isset($_POST['send'])) {
			$this->form_validation->set_rules('email', 'lang:bf_email', 'required|trim|valid_email');

			if ($this->form_validation->run()) {
				// Form validated. Does the user actually exist?
				$user = $this->user_model->find_by('email', $_POST['email']);
				if ($user === false) {
					Template::set_message('Cannot find that email in our records.', 'danger');
				} else {
					$activation = $this->user_model->set_activation($user->user_id);
					$message = $activation['message'];
					$error = $activation['error'];

					Template::set_message($message, $error ? 'danger' : 'success');
				}
			} else {
				Template::set_message(validation_errors(), 'danger');
			}
		}

		Template::set_view('users/resend_activation');
		Template::set('page_title', 'Activate Account');
		Template::render('account');
	}

	// -------------------------------------------------------------------------
	// Private Methods
	// -------------------------------------------------------------------------

	/**
	 * Save the user.
	 *
	 * @param  string  $type            The type of operation ('insert' or 'update').
	 * @param  integer $id              The id of the user (ignored on insert).
	 *
	 * @return boolean/integer The id of the inserted user or true on successful
	 * update. False if the insert/update failed.
	 */
	private function saveUser($type = 'insert', $id = 0)
	{
		if ($type != 'insert') {
			if ($id == 0) {
				$id = $this->current_user->user_id;
			}
			$_POST['id'] = $id;

			// Security check to ensure the posted id is the current user's id.
			if ($_POST['id'] != $this->current_user->user_id) {
				$this->form_validation->set_message('email', 'lang:us_invalid_userid');
				return false;
			}
		}
		dump($this->user_model->get_validation_rules($type));
		$this->form_validation->set_rules($this->user_model->get_validation_rules($type));

		$usernameRequired = '';
		if ($this->settings_lib->item('auth.login_type') == 'username'
			|| $this->settings_lib->item('auth.use_usernames')
		) {
			$usernameRequired = 'required|';
		}

		// If a value has been entered for the password, pass_confirm is required.
		// Otherwise, the pass_confirm field could be left blank and the form validation
		// would still pass.
		if ($type != 'insert' && $this->input->post('password')) {
			$this->form_validation->set_rules('pass_confirm', 'lang:bf_password_confirm', "required|matches[password]");
		}

		// Setting the payload for Events system.
		$payload = array('user_id' => $id, 'data' => $this->input->post());

		// Event "before_user_validation" to run before the form validation.
		Events::trigger('before_user_validation', $payload);

		if ($this->form_validation->run() === false) {
			return false;
		}

		// Compile our core user elements to save.
		$data = $this->user_model->prep_data($this->input->post());
		dump($data);
		$result = false;

		if ($type == 'insert') {
			$activationMethod = $this->settings_lib->item('auth.user_activation_method');
			if ($activationMethod == 0) {
				// No activation method, so automatically activate the user.
				$data['active'] = 1;
			}

			$id = $this->user_model->insert($data);
			if (is_numeric($id)) {
				$result = $id;
			}
		} else {
			$result = $this->user_model->update($id, $data);
		}

		// Add result to payload.
		$payload['result'] = $result;
		// Trigger event after saving the user.
		Events::trigger('save_user', $payload);

		return $result;
	}

	private function check_current_email()
	{
		if (! isset($this->current_user)) {
			if (! class_exists('Auth')) {
				$this->load->library('users/Auth');
			}
			$current_user = $this->auth->user();
		} else {
			$current_user = $this->current_user;
		}
		$data = [
			'public_domain' => false,
			'domain_name' => ''
		];

		$current_email = $current_user->email;
		$domain_name = substr(strrchr($current_email, "@"), 1);
		// get email domain name
		$data['domain_name'] = $domain_name;
		// detect if it is a public domain name
		$is_public_domain_name = $this->db->select('count(*) as count')
										->where('domain', $domain_name)
										->get('public_email_domains')->row()->count > 0 ? true : false;
		if ($is_public_domain_name) {
			// if it is a public domain name, check if it is in existed organization
			$data['public_domain'] = true;
			$user_organization = $this->db->select('o.url')
											->from('organizations o')
											->join('user_to_organizations uto', 'o.organization_id = uto.organization_id', 'left')
											->where('uto.user_id', $current_user->user_id)
											->get()->row();
			// if it is in existed organization, not allow to create a new organization
			if (! empty($user_organization)) {
				Template::redirect('/'); // organization url
			} else {
				Template::redirect('/organization/create');
			}
		} else {
			// if it is not a public domain name, check if it is in existed organization
			$existed_domain_name = $this->db->select('od.*, o.url, r.role_id')
											->from('organization_domains od')
											->join('organizations o', 'o.organization_id = od.organization_id', 'left')
											->join('roles r', 'r.organization_id = o.organization_id AND join_default = \'1\'', 'left')
											->where('od.domain', $domain_name)
											->get()->row();
			// if it is in existed organization, not allow to create a new organization
			if ($existed_domain_name) {
				$in_organization = $this->db->select('count(*) as count')
											->from('user_to_organizations')
											->where('user_id', $current_user->user_id)
											->get()->row()->count > 0 ? true : false;
				if (! $in_organization) {
					$this->db->insert('user_to_organizations', [
						'user_id' => $current_user->user_id,
						'organization_id' => $existed_domain_name->organization_id,
						'role_id' => $existed_domain_name->role_id
					]);
				}

				$organization_url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $existed_domain_name->url . '.' . $_SERVER['SERVER_NAME'];
				Template::redirect($organization_url);
			} else {
				Template::redirect('/organization/create');
			}
		}
	}
}
