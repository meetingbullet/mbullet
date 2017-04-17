<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Authenticated Controller
 *
 * Provides a base class for all controllers that must check user login status.
 *
 * @package  Bonfire\Core\Controllers\Authenticated_Controller
 * @category Controllers
 * @author   Bonfire Dev Team
 * @link     http://cibonfire.com/docs
 *
 */
class Authenticated_Controller extends Base_Controller
{
	protected $require_authentication = true;
	// result return from function check_current_email()
	protected $check_current_email_result;

	//--------------------------------------------------------------------------

	/**
	 * Class constructor setup login restriction and load various libraries
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->autoload['helpers'][]   = 'form';
		$this->autoload['libraries'][] = 'Template';
		$this->autoload['libraries'][] = 'Assets';
		$this->autoload['libraries'][] = 'form_validation';
		$this->autoload['libraries'][] = 'contexts';
		$this->autoload['libraries'][] = 'users/auth';

		parent::__construct();
		
		$this->redirect_to_organization_url();

		$this->form_validation->CI =& $this;
		$this->form_validation->set_error_delimiters('', '');

		// check current user email
		$this->check_current_email_result = $this->check_current_email();
		// Basic setup
		Template::set_theme('user', 'junk');
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
											->where('uto.enabled', 1)
											->get()->row();
			// if it is in existed organization, not allow to create a new organization
			if (! $user_organization) {
				if ($this->router->fetch_module() != 'organization' && $this->router->fetch_class() != 'Organization' && $this->router->fetch_method() !== 'create') {
					redirect('/organization/create');
				}
			}
		} else {
			// if it is not a public domain name, check if it is in existed organization
			$existed_domain_name = $this->db->select('od.*, o.url')
											->from('organization_domains od')
											->join('organizations o', 'o.organization_id = od.organization_id', 'left')
											->join('user_to_organizations uto', 'o.organization_id = uto.organization_id', 'left')
											->where('od.domain', $domain_name)
											->where('uto.enabled', 1)
											->get()->row();
			// if it is in existed organization, not allow to create a new organization
			if (! $existed_domain_name) {
				if ($this->router->fetch_module() != 'organization' && $this->router->fetch_class() != 'Organization' && $this->router->fetch_method() !== 'create') {
					redirect('/organization/create');
				}
			}
		}

		return $data;
	}

	private function redirect_to_organization_url()
	{
		if (is_null($this->current_user->current_organization_id)) {
			// get main domain
			$current_domain = $_SERVER['SERVER_NAME'];
			$parsed_url = explode('.', $current_domain);
			$main_domain_parts = [];
			for ($i = (count($parsed_url) - MAIN_DOMAIN_PARTS); $i < count($parsed_url); $i++) {
				$main_domain_parts[] = $parsed_url[$i];
			}
			$main_domain = implode('.', $main_domain_parts);

			// get sub domain
			$orgs = $this->db->select('o.organization_id, o.url')
							->from('organizations o')
							->join('user_to_organizations uo', 'o.organization_id = uo.organization_id', 'left')
							->where('uo.user_id', $this->current_user->user_id)
							->where('uo.enabled', 1)
							->get();
			if ($orgs->num_rows() > 1) {
				redirect('/organization/choose');
			} elseif ($orgs->num_rows() == 1) {
				$sub = $orgs->row()->url;
				// redirect to organization domain if current domain is incorrect
				if (isset($this->requested_page) && !empty($this->requested_page))
					redirect((is_https() ? 'https://' : 'http://') . $sub . '.' . $main_domain . '/' . $this->uri->uri_string());
				else
					redirect((is_https() ? 'https://' : 'http://') . $sub . '.' . $main_domain . '/' . DEFAULT_LOGIN_LOCATION);
			}
		}
	}
}