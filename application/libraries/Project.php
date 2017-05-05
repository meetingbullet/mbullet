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

	public function has_permission($object, $object_key, $permission_name)
	{
		//get project_id
		switch ($object) {
			case 'project':
				$query = $this->ci->db->select('project_id')->from('projects')->where('project_key', $object_key)->get();
				if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
				else return false;
				break;
			case 'action':
				$query = $this->ci->db->select('project_id')->from('actions')->where('action_key', $object_key)->get();
				if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
				else return false;
				break;
			case 'step':
				$query = $this->ci->db->select('a.project_id')->from('actions a')->join('steps s', 'a.action_id = s.action_id')->where('s.step_key', $object_key)->get();
				if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
				else return false;
				break;
			case 'task':
				$query = $this->ci->db->select('a.project_id')->from('actions a')->join('steps s', 'a.action_id = s.action_id')->join('tasks t', 't.step_id = s.step_id')->where('t.task_key', $object_key)->get();
				if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
				else return false;
				break;
			default:
				return false;
		}

		//check logged user is project owner ?
		$query = $this->ci->db->select('project_id')->where('project_id', $project_id)->where('owner_id', $this->ci->auth->user()->user_id)->get('projects');
		if ($query->num_rows() > 0) return true;

		//check logged user is project member ?
		$query = $this->ci->db->select('project_id')->where('project_id', $project_id)->where('user_id', $this->ci->auth->user()->user_id)->get('project_members');
		if ($query->num_rows() > 0) return true;

		//check logged user has permission ?
		if (has_permission($permission_name)) return true;

		return false;
	}
}