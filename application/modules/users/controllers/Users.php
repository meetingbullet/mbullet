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
		$this->load->helper('mb_general');
		$this->load->library('form_validation');

		$this->load->model('user_model');
		$this->load->library('auth');

		$this->load->library('domain');
		$this->lang->load('users');

		$this->siteSettings = $this->settings_lib->find_all();
		if ($this->siteSettings['auth.password_show_labels'] == 1) {
			Assets::add_module_js('users', 'password_strength.js');
			Assets::add_module_js('users', 'jquery.strength.js');
		}
		//load google api config file
		$this->config->load('google_api');
		// Set up login using google account
		Assets::add_module_js('users', 'google_api.js');
		Assets::add_module_js('users', 'users.js');
		Template::set('use_google_api', true);
		Template::set('client_id', $this->config->item('client_id'));
	}

	/*
		Invitation game
		Save invite code in session for after register or login One Direction
	*/
	public function invitation($type = '', $invite_code = '', $action = '')
	{
		if (empty($type) ||  empty($invite_code)) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$this->session->set_userdata('invite_code', $invite_code);
		$this->session->set_userdata('invite_type', $type);
		$this->session->set_userdata('invite_action', $action);

		switch ($type) {
			case 'organization': 
				Template::set_message(lang('us_view_your_invitation'), 'info');
				redirect(LOGIN_URL);
				break;
			case 'project': 
				if ($this->auth->is_logged_in()) { // echo site_url('invite/confirm_project/' . $invite_code . '/' . $action); die;
					redirect(site_url('invite/confirm_project/' . $invite_code . '/' . $action));
				} else {
					Template::set_message(lang('us_view_your_invitation'), 'info');
					redirect(LOGIN_URL);
				}
			case 'meeting':
				redirect(DEFAULT_LOGIN_LOCATION);
			default:
				redirect(DEFAULT_LOGIN_LOCATION);
		}
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
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		// include google client api
		require_once APPPATH . 'modules/users/libraries/google-api-client/vendor/autoload.php';
		$client_id = $this->config->item('client_id');
		$client_secret = $this->config->item('client_secret');
		$redirect_uri = (is_https() ? 'https://' : 'http://') . $this->domain->get_main_domain() . '/login';

		$client = new Google_Client();
		$client->setAccessType("offline");
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		//$client->setIncludeGrantedScopes(true);
		$client->addScope(Google_Service_Calendar::CALENDAR_READONLY);
		$client->addScope("email");
		$client->addScope("profile");

		// signin by google url
		Template::set('auth_url', $client->createAuthUrl());

		if (isset($_GET['error'])) {
			Template::set_message(lang('us_failed_login_attempts'), 'danger');
		}

		// login by google account
		if (isset($_GET['code']) && isset($_GET['timezone'])) {
			// merge user & add user to db
			$service = new Google_Service_Oauth2($client);
			try {
				$ret = $client->authenticate($_GET['code']);
				if (isset($ret) && ($ret['error'])) {
					throw new Exception(lang('us_login_google_code_error'));
				}
				$token = $client->getAccessToken();
				$google_user = $service->userinfo->get();

				$user = $this->user_model->find_by('google_id', $google_user->id);
				if (! $user) {
					// add google user to db
					$added = $this->user_model->insert([
						'email' => $google_user->email,
						'google_id' => $google_user->id,
						'google_refresh_token' => $token['refresh_token'],
						'google_id_token' => $token['id_token'],
						'first_name' => $google_user->given_name,
						'last_name' => $google_user->family_name,
						'avatar' => $google_user->picture,
						'active' => 1,
						'timezone' => $this->input->get('timezone')
					]);

					if (! $added) {
						throw new Exception(lang('us_failed_login_attempts'));
					}
				} else {
					$update_data = [
						'email' => $google_user->email,
						'google_id_token' => $token['id_token'],
						'first_name' => $google_user->given_name,
						'last_name' => $google_user->family_name,
						'avatar' => $google_user->picture,
						'active' => 1,
						'timezone' => $this->input->get('timezone')
					];

					if (! empty($token['refresh_token'])) {
						$update_data['google_refresh_token'] = $token['refresh_token'];
					}
					// merge user if email exist
					$updated = $this->user_model->update($user->user_id, $update_data);

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
			&& isset($_GET['timezone'])
			&& !isset($google_login_error)
			&& true === $this->auth->login(
				$google_user->email,
				null,
				true,
				true,
				$token['id_token']
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
			$this->join_into_organization();
			// redirect('/');
		}

		Assets::add_css('font-awesome/css/font-awesome.min.css');
		Template::set('page_title', 'Login');
		Template::render('account');
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
		redirect($this->domain->get_main_url());
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

		$user_id = $this->current_user->user_id;
		$user = $this->user_model->find($user_id);

		if (! empty($user->new_email) && (strtotime('now') < strtotime($user->new_email_expired_on))) {
			$user->chosen_email = $user->new_email;
		} else {
			$user->chosen_email = $user->email;
			$this->user_model->update($user_id, [
				'new_email' => null,
				'new_email_expired_on' => null,
				'new_email_hash' => null
			]);
		}

		$old_password_matched = true;
		if (isset($_POST['save']) || isset($_POST['save_password'])) {
			$rules = $this->user_model->get_validation_rules();
			$this->form_validation->set_rules(isset($_POST['save']) ? $rules['profile'] : $rules['change_password']);

			if (trim($this->input->post('new_email')) == $user->email || trim($this->input->post('new_email')) == $user->new_email) {
				unset($_POST['new_email']);
			}

			if ($this->form_validation->run() !== false) {
				if (isset($_POST['save_password'])) {
					if (! $this->auth->check_password($this->input->post('current_password'), $user->password_hash)) {
						$old_password_matched = false;
					}
				}

				if ($old_password_matched) {
					$data = $this->user_model->prep_data($this->input->post());
					if (isset($_POST['save'])) {
						if ($data['avatar']['size'] > 0) {
							if ($user->avatar) {
								@unlink($upload_config['upload_path'] . $user->avatar);
							}

							$upload_config = $this->config->load('upload');
							$this->load->library('upload', $upload_config);
							$this->upload->do_upload('avatar');
							$data['avatar'] = $this->upload->data();
							$data['avatar'] = $data['avatar']['file_name'];
						} else {
							unset($data['avatar']);
						}
					}

					// User cannot change his email
					unset($data['email']);
					if (! empty($data['new_email'])) {
						$this->load->helper('string');
						$data['new_email_hash'] = sha1(random_string('alnum', 40) . $data['new_email']);
						$data['new_email_expired_on'] = date('Y-m-d H:i:s', strtotime('+1 week'));
					}

					if ($this->input->post('new_password')) {
						$password = $this->auth->hash_password($this->input->post('new_password'));
						$data['password_hash'] = $password['hash'];
					}

					$updated = $this->user_model->skip_validation(true)->update($user_id, $data);

					if (! $updated) {
						Template::set_message(lang('us_profile_updated_error'), 'danger');
					} else {
						Template::set_message(lang('us_profile_updated_success'), 'success');
						if (! empty($data['new_email'])) {
							// Now send the email
							$this->load->library('emailer/emailer');
							$this->load->library('parser');
							$email_template = $this->db->where('email_template_key', 'UPDATE_EMAIL')
													->where('language_code', 'en_US')
													->get('email_templates')->row();
							if (! empty($email_template)) {
								$email_data = array(
									'to'	  => $data['new_email'],
									'subject' => $email_template->email_title,
									'message' => $this->parser->parse_string(html_entity_decode(nl2br($email_template->email_template_content)), [
										'URL' => site_url('users/confirm_change_email/' . $data['new_email_hash']),
										'LABEL' => site_url('users/confirm_change_email/' . $data['new_email_hash'])
									], true),
								);
								$this->emailer->send($email_data);
							}
							$user->chosen_email = $data['new_email'];
						}
					}
				} else {
					Template::set_message(lang('us_wrong_current_password'), 'danger');
				}
			} else {
				Template::set_message(validation_errors(), 'danger');
			}
		}

		Assets::add_js($this->load->view('profile_js', null, true), 'inline');
		Assets::add_js($this->load->view('resend_confirm_change_email_js', null, true), 'inline');
		Template::set('old_password_matched', $old_password_matched);
		Template::set('user', $user);
		Template::set('languages', unserialize($this->settings_lib->item('site.languages')));
		Template::set_view('profile');
		Template::render('account');
	}

	public function confirm_change_email($code = '')
	{
		// Make sure the user is logged in.
		$this->auth->restrict();
		$this->set_current_user();

		$user_id = $this->current_user->user_id;
		$user = $this->user_model->find($user_id);
		$code = trim($code);

		if ((! empty($code)) && $user->new_email_hash == $code && (strtotime('now') < strtotime($user->new_email_expired_on))) {
			$data = [
				'email' => $user->new_email,
				'new_email' => null,
				'new_email_expired_on' => null,
				'new_email_hash' => null
			];

			$updated = $this->user_model->update($user_id, $data);
			if ($updated) {
				Template::set_message(lang('us_change_email_success'), 'success');
				if (! empty($user->google_refresh_token)) {
					require_once APPPATH . 'modules/users/libraries/google-api-client/vendor/autoload.php';
					$client_id = $this->config->item('client_id');
					$client_secret = $this->config->item('client_secret');

					$client = new Google_Client();
					$client->setAccessType("offline");
					$client->setClientId($client_id);
					$client->setClientSecret($client_secret);
					$client->refreshToken($user->google_refresh_token);
					$token = $client->getAccessToken();
					$client->revokeToken($token);
				}
				$this->logout();
			} else {
				$error = true;
			}
		} else {
			$error = true;
		}

		Assets::add_js($this->load->view('resend_confirm_change_email_js', null, true), 'inline');
		Template::set('message', ! empty($error) ? lang('us_change_email_fail') : '');
		Template::render('account');
	}

	public function get_current_user_info() {
		if ((! $this->auth->is_logged_in()) || (! $this->input->is_ajax_request())) {
			echo json_encode([
				'status' => 0,
			]);
			exit;
		}

		$this->set_current_user();
		$user_id = $this->current_user->user_id;
		$user = $this->user_model->find($user_id);

		echo json_encode([
			'status' => 1,
			'data' => $user
		]);
		exit;
	}

	public function resend_confirm_change_email()
	{
		if ((! $this->auth->is_logged_in()) || (! $this->input->is_ajax_request())) {
			echo json_encode([
				'status' => 0,
				'message' => lang('us_resend_confirm_fail')
			]);
			exit;
		}

		$this->set_current_user();
		$user_id = $this->current_user->user_id;
		$user = $this->user_model->find($user_id);

		$this->load->helper('string');
		$data['new_email_hash'] = sha1(random_string('alnum', 40) . $user->new_email);
		$data['new_email_expired_on'] = date('Y-m-d H:i:s', strtotime('+1 week'));

		$updated = $this->user_model->skip_validation(true)->update($user_id, $data);
		if (! $updated) {
			echo json_encode([
				'status' => 0,
				'message' => lang('us_resend_confirm_fail')
			]);
			exit;
		}

		$this->load->library('emailer/emailer');
		$this->load->library('parser');
		$email_template = $this->db->where('email_template_key', 'UPDATE_EMAIL')
								->where('language_code', 'en_US')
								->get('email_templates')->row();
		if (empty($email_template)) {
			echo json_encode([
				'status' => 0,
				'message' => lang('us_resend_confirm_fail')
			]);
			exit;
		}
		$email_data = array(
			'to'	  => $user->new_email,
			'subject' => $email_template->email_title,
			'message' => $this->parser->parse_string(html_entity_decode(nl2br($email_template->email_template_content)), [
				'URL' => site_url('users/confirm_change_email/' . $data['new_email_hash']),
				'LABEL' => site_url('users/confirm_change_email/' . $data['new_email_hash'])
			], true),
		);
		$sent = $this->emailer->send($email_data);
		if (! $sent) {
			echo json_encode([
				'status' => 0,
				'message' => lang('us_resend_confirm_fail')
			]);
			exit;
		}

		echo json_encode([
				'status' => 1,
				'message' => lang('us_resend_confirm_success')
			]);
		exit;
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
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		if ($this->input->post()) {
			$rules = $this->user_model->get_validation_rules();
			$this->form_validation->set_rules($rules['register']);

			if ($this->form_validation->run() !== false) {
				redirect('/users/create_profile?email=' . urlencode($this->input->post('email')));
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
			redirect('/');
		}

		if ($this->auth->is_logged_in() === true) {
			redirect(DEFAULT_LOGIN_LOCATION);
		}

		$upload_config = $this->config->load('upload');
		$this->load->library('upload', $upload_config);

		if ($this->input->post()) {
			$rules = $this->user_model->get_validation_rules();
			// custom error message for confirm terms
			$rules['create_profile'][1]['errors']['required'] = lang('form_validation_confirm_required');
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
						'organization' => $this->input->post('org')
					];

					if (! empty($this->input->post('timezone'))) {
						$data['timezone'] = $this->input->post('timezone');
					}

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

		Assets::add_js($this->load->view('create_profile_js', [], true), 'inline');
		Template::render('account');
	}
	/**
	 * Display the terms of service.
	 *
	 *
	 * @return void
	 */
	public function terms()
	{
		Template::render('blank');
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
		// if ($this->auth->is_logged_in() !== false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

		if (isset($_POST['send'])) {
			// Validate the form to ensure a valid email was entered.
			$this->form_validation->set_rules('email', 'lang:bf_email', 'required|trim|valid_email');
			if ($this->form_validation->run() !== false) {
				// Validation passed. Does the user actually exist?
				$user = $this->user_model->find_by('email', $this->input->post('email'));
				if ($user === false) {
					// No user found with the entered email address.
					if (! $this->input->is_ajax_request()) {
						Template::set_message(lang('us_invalid_email'), 'danger');
					} else {
						echo json_encode([
							'status' => 0,
							'message' => lang('us_invalid_email')
						]);
						exit;
					}
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
						if (! $this->input->is_ajax_request()) {
							Template::set_message(lang('us_reset_pass_message'), 'success');
						} else {
							echo json_encode([
								'status' => 1,
								'message' => lang('us_reset_pass_message')
							]);
							exit;
						}
					} else {
						if (! $this->input->is_ajax_request()) {
							Template::set_message(lang('us_reset_pass_error') . $this->emailer->error, 'danger');
						} else {
							echo json_encode([
								'status' => 0,
								'message' => lang('us_reset_pass_error') . $this->emailer->error
							]);
							exit;
						}
					}
				}
			} else {
				if (! $this->input->is_ajax_request()) {
					Template::set_message(validation_errors(), 'danger');
				} else {
					echo json_encode([
						'status' => 0,
						'message' => validation_errors()
					]);
					exit;
				}
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
		// if ($this->auth->is_logged_in() !== false) {
		// 	redirect(DEFAULT_LOGIN_LOCATION);
		// }

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
			redirect(LOGIN_URL);
		}

			// Handle the form
		if (isset($_POST['set_password'])) {
			$this->form_validation->set_rules('password', 'lang:bf_password', 'required|max_length[120]|valid_password');
			$this->form_validation->set_rules('pass_confirm', 'lang:bf_password_confirm', 'required|matches[password]');

			if ($this->form_validation->run() !== false) {
				// The user model will create the password hash.

				$hash_password = $this->auth->hash_password($this->input->post('password'));
				$data = array(
					'password_hash' => $hash_password['hash'],
					'reset_by' => 0,
					'reset_hash' => null,
					'force_password_reset' => 0,
				);

				if ($this->user_model->update($this->input->post('user_id'), $data)) {
					log_activity($this->input->post('user_id'), lang('us_log_reset'), 'users');

					Template::set_message(lang('us_reset_password_success'), 'success');
					redirect(LOGIN_URL);
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
			redirect(LOGIN_URL);
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
			redirect(LOGIN_URL);
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

	private function join_into_organization()
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
				redirect(DEFAULT_LOGIN_LOCATION); // organization url
			} else {
				redirect('/organization/create');
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

				redirect(DEFAULT_LOGIN_LOCATION);
			} else {
				redirect('/organization/create');
			}
		}
	}
}
