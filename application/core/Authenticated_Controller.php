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

        parent::__construct();

        $this->form_validation->CI =& $this;
        $this->form_validation->set_error_delimiters('', '');
		//load google api config file
		$this->config->load('users/google_api');
		// Set up login using google account
		Assets::add_module_js('users', 'google_api.js');
		Template::set('use_google_api', true);
		Template::set('client_id', $this->config->item('client_id'));
    }
}
