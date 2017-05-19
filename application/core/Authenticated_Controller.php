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
		$this->autoload['helpers'][]   = 'mb_general';
		$this->autoload['libraries'][] = 'Template';
		$this->autoload['libraries'][] = 'Assets';
		$this->autoload['libraries'][] = 'form_validation';
		$this->autoload['libraries'][] = 'contexts';
		$this->autoload['libraries'][] = 'users/auth';

		parent::__construct();
		$this->redirect_to_organization_url();
		$this->goto_create_organization();
		$this->get_navigation_project_list();

		$this->form_validation->CI =& $this;
		$this->form_validation->set_error_delimiters('', '');

		// Basic setup
		Template::set_theme('user', 'junk');
	}

	private function goto_create_organization()
	{
		// Invitation game
		if ($invite_code = $this->session->userdata('invite_code')) {
			$this->session->set_userdata('invite_code', NULL);
			redirect('/invite/confirm/' . $invite_code);
			return;
		}

		// Stay in the invite confirm page
		if (strstr($this->uri->uri_string(), 'invite/confirm')) {
			return;
		}


		// If user still access to an organization, can not create new anymore
		$orgs = $this->db->select('count(*) AS total')
							->from('organizations o')
							->join('user_to_organizations uo', 'o.organization_id = uo.organization_id', 'left')
							->where('uo.user_id', $this->current_user->user_id)
							->where('uo.enabled', 1)
							->get()->row();
		if ($orgs->total == 0) {
			if ($this->router->fetch_module() != 'organization' && $this->router->fetch_class() != 'Organization' && $this->router->fetch_method() !== 'create') {
				redirect('/organization/create');
			}
		}
	}

	private function redirect_to_organization_url()
	{
		if (is_null($this->current_user->current_organization_id)) {
			// get main domain
			$this->load->library('domain');
			$main_domain = $this->domain->get_main_domain();
			
			// get sub domain
			$orgs = $this->db->select('o.organization_id, o.url')
							->from('organizations o')
							->join('user_to_organizations uo', 'o.organization_id = uo.organization_id', 'left')
							->where('uo.user_id', $this->current_user->user_id)
							->where('uo.enabled', 1)
							->get();
			if ($orgs->num_rows() > 1) {
				if ($this->router->fetch_module() != 'organization' && $this->router->fetch_class() != 'Organization' && $this->router->fetch_method() !== 'choose') {
					redirect('/organization/choose');
				}
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

	private function get_navigation_project_list()
	{
		$this->current_user->projects = $this->db->select('name')
													->join('project_members pm', 'pm.project_id = projects.project_id', 'LEFT')
													->where('owner_id', $this->current_user->user_id)
													->or_where('user_id', $this->current_user->user_id)
													->get('projects')
													->result();
	}
}