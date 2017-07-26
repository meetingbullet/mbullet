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
		$this->autoload['libraries'][] = 'Mb_project';
		$this->autoload['libraries'][] = 'Template';
		$this->autoload['libraries'][] = 'Assets';
		$this->autoload['libraries'][] = 'form_validation';
		$this->autoload['libraries'][] = 'contexts';
		$this->autoload['libraries'][] = 'users/auth';

		parent::__construct();
		$this->check_user_enabled();
		$this->redirect_to_invitation() || $this->redirect_to_organization_url();
		$this->goto_create_organization();
		$this->get_navigation_project_list();

		$this->form_validation->CI =& $this;
		$this->form_validation->set_error_delimiters('', '');

		// Basic setup
		Template::set_theme('user', 'junk');
	}

	private function check_user_enabled()
	{
		if (is_null($this->current_user->current_organization_id)) {
			if (! is_null($this->current_organization_url) && ! (strstr($this->uri->uri_string(), 'invite/confirm') || $this->session->userdata('invite_code'))) {
				// user logged in but not choose organization or user can not access organization or user is not part of organization
				$uo = $this->db->select('uo.enabled')
							->from('user_to_organizations uo')
							->join('organizations o', 'o.organization_id = uo.organization_id', 'inner')
							->where('uo.user_id', $this->current_user->user_id)
							->where('o.url', $this->current_organization_url)
							->get()->row();

				$organization = $this->db->select('name')
										->where('url', $this->current_organization_url)
										->get('organizations')->row();

				if (! empty($uo)) {
					if ($uo->enabled == 0) {
						Template::set_message(sprintf(lang('user_is_disabled_from_organization'), ucfirst(! empty($organization) ? $organization->name : $this->current_organization_url)), 'danger');
					}
				} else {
					Template::set_message(lang('use_is_out_of_organization'), 'danger');
				}
			}
		} else {
			// user's still logging in
			$uo = $this->db->select('enabled')
							->from('user_to_organizations')
							->where('user_id', $this->current_user->user_id)
							->where('organization_id', $this->current_user->current_organization_id)
							->get()->row();

			$organization = $this->db->select('name')
									->where('organization_id', $this->current_user->current_organization_id)
									->get('organizations')->row();

			if ($uo->enabled == 0) {
				Template::set_message(sprintf(lang('user_is_disabled_from_organization'), ucfirst(! empty($organization) ? $organization->name : $this->current_organization_url)), 'danger');
				$this->current_user->current_organization_id = null;
			}
		}
	}

	private function goto_create_organization()
	{
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
			if (($this->router->fetch_module() != 'organization' && $this->router->fetch_class() != 'Organization' && $this->router->fetch_method() !== 'create')
			&& ($this->router->fetch_module() != 'meeting' && $this->router->fetch_class() != 'Meeting' && $this->router->fetch_method() !== 'confirm')) {
				redirect('/organization/create');
			}
		}
	}

	private function redirect_to_organization_url()
	{
		// Stay in the invite confirm page
		if (strstr($this->uri->uri_string(), 'invite/confirm')) {
			return;
		}

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
					Template::redirect((is_https() ? 'https://' : 'http://') . $sub . '.' . $main_domain . '/' . $this->uri->uri_string());
				else
					Template::redirect((is_https() ? 'https://' : 'http://') . $sub . '.' . $main_domain . '/' . DEFAULT_LOGIN_LOCATION);
			}
		}
	}


	private function redirect_to_invitation()
	{
		// Invitation game
		$invite_code = $this->session->userdata('invite_code');
		$invite_type = $this->session->userdata('invite_type');
		$invite_action = $this->session->userdata('invite_action');

		if ($invite_code && $invite_type) {
			$this->session->set_userdata('invite_code', null);
			$this->session->set_userdata('invite_type', null);
			$this->session->set_userdata('invite_action', null);

			if ( $invite_type == 'organization' ) {
				redirect('/invite/confirm/' . $invite_code);
			}

			if ( $invite_type == 'project') {
				redirect('/invite/confirm_project/' . $invite_code . '/' . $invite_action);
			}

			return true;
		}

		return false;
	}
}