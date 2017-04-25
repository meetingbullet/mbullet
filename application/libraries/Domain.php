<?php defined('BASEPATH') || exit('No direct script access allowed');

class Domain
{
	private $ci;
	
	public function __construct()
	{
		$this->ci =& get_instance();
	}

	public function get_main_domain()
	{
		$current_domain = $_SERVER['SERVER_NAME'];
		$parsed_url = explode('.', $current_domain);
		$main_domain_parts = [];
		for ($i = (count($parsed_url) - MAIN_DOMAIN_PARTS); $i < count($parsed_url); $i++) {
			$main_domain_parts[] = $parsed_url[$i];
		}
		$main_domain = implode('.', $main_domain_parts);

		return $main_domain;
	}

	public function get_main_url()
	{
		return (is_https() ? 'https://' : 'http://') . $this->get_main_domain();
	}

	public function get_organization_url($organization_id)
	{
		$query = $this->ci->db->select('url')->from('organizations')->where('organization_id', $organization_id)->get();

		if ($query->num_rows() == 1) {
			$sub = $query->row()->url;
			return (is_https() ? 'https://' : 'http://') . strtolower($sub) . '.' . $this->get_main_domain();
		} else {
			return null;
		}
	}
}
