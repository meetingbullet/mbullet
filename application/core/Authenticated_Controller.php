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
											->get()->row();
			// if it is in existed organization, not allow to create a new organization
			if (! $user_organization) {
				if ($this->router->fetch_module() != 'organization' && $this->router->fetch_class() != 'Organization' && $this->router->fetch_method() !== 'create') {
					Template::redirect('/organization/create');
				}
			} else {
				if ($this->router->fetch_module() != null && $this->router->fetch_class() != 'Home' && $this->router->fetch_method() !== 'index') {
					Template::redirect('/');
				}
			}
		} else {
			// if it is not a public domain name, check if it is in existed organization
			$existed_domain_name = $this->db->select('od.*, o.url')
											->from('organization_domains od')
											->join('organizations o', 'o.organization_id = od.organization_id', 'left')
											->where('od.domain', $domain_name)
											->get()->row();
			// if it is in existed organization, not allow to create a new organization
			if (! $existed_domain_name) {
				if ($this->router->fetch_module() != 'organization' && $this->router->fetch_class() != 'Organization' && $this->router->fetch_method() !== 'create') {
					Template::redirect('/organization/create');
				}
			} else {
				if ($this->router->fetch_module() != null && $this->router->fetch_class() != 'Home' && $this->router->fetch_method() !== 'index') {
					Template::redirect('/');
				}
			}
		}

		return $data;
	}
}