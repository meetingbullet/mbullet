<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Meeting Bullet
 *
 * @package   Meeting Bullet
 * @author    DatLS
 * @copyright Copyright (c) 2017, SGS Engineering Team
 * @since     Version 1.0
 */

/**
 * Multi Organization (Multi subdomains) Hooks
 */
class Multi_orgs_hooks
{
	/**
	 * @var object The CodeIgniter core object.
	 */
	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * Check and set current organization_id to $this->ci variable
	 *
	 * @param int $main_domain_parts - example meetingbullet.com has 2 parts, meetingbullet.com.vn has 3 parts
	 * @return void
	 */
	public function check_current_organization($main_domain_parts = 2)
	{
		$this->ci->current_organization_url = null;

		if (isset($_SERVER['SERVER_NAME'])) {
			$current_domain = $_SERVER['SERVER_NAME'];
			$parsed_url = explode('.', $current_domain);
			if (count($parsed_url) - $main_domain_parts - 1 >= 0) {
				$subdomain = $parsed_url[count($parsed_url) - $main_domain_parts - 1];
				$this->ci->current_organization_url = $subdomain;
			}
		}
	}
}
