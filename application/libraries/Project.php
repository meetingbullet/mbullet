<?php defined('BASEPATH') || exit('No direct script access allowed');

class Project
{
	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	public function get_next_key($parent_key, $table = "")
	{
		if (empty($table)) {
			$keys = explode('-', $parent_key);
			if (empty($keys)) {
				return false;
			}

			if (count($keys) == 1) {
				$table = 'actions';
			}

			if (count($keys) == 2) {
				$table = 'steps';
			}

			if (count($keys) == 3) {
				$table = 'tasks';
			}
		}

		$last_key = $this->ci->db->select('MAX(CAST(REPLACE(`' . rtrim($table, 's') . '_key`, \'' . $parent_key . '-' . '\', \'\') AS UNSIGNED)) AS `last_key`', false)
								->like('`' . rtrim($table, 's') . '_key`', $parent_key . '-', 'after')
								->get($table)
								->row()->last_key;

		return $parent_key . '-' . (empty($last_key) ? 1 : ($last_key + 1));
	}
}