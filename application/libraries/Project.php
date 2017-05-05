<?php defined('BASEPATH') || exit('No direct script access allowed');

class Project
{
	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * Get next key for Project, Action, Step, Task
	 *
	 * @param string $parent_key
	 * @param string $table
	 * @return string
	 */
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
		} elseif ($table != 'actions' && $table != 'steps' && $table != 'tasks') {
			return false;
		}

		
		$query = $this->ci->db->select('MAX(CAST(REPLACE(`' . rtrim($table, 's') . '_key`, \'' . $parent_key . '-' . '\', \'\') AS UNSIGNED)) AS `last_key`', false)
								->like('`' . rtrim($table, 's') . '_key`', $parent_key . '-', 'after')
								->get($table);
		if ($query->num_rows() > 0) {
			$last_key = $query->row()->last_key;
			return $parent_key . '-' . (empty($last_key) ? 1 : ($last_key + 1));
		} else {
			return false;
		}
	}

	/**
	 * Get object ID from object key (Organization checked)
	 *
	 * @param string $object_type
	 * @param string $object_key
	 * @return int if found, otherwise return false
	 */
	public function get_object_id($object_type, $object_key)
	{
		switch ($object_type) {
			case 'project':
				$query = $this->ci->db->select('project_id')->from('projects')->where('cost_code', $object_key)->where('organization_id', $this->ci->auth->user()->current_organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->project_id;
				else return false;
				break;
			case 'action':
				$query = $this->ci->db->select('a.action_id')->from('actions a')->join('projects p', 'p.project_id = a.project_id')->where('a.action_key', $object_key)->where('p.organization_id', $this->ci->auth->user()->current_organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->action_id;
				else return false;
				break;
			case 'step':
				$query = $this->ci->db->select('s.step_id')->from('steps s')->join('actions a', 'a.action_id = s.action_id')->join('projects p', 'p.project_id = a.project_id')->where('s.step_key', $object_key)->where('p.organization_id', $this->ci->auth->user()->current_organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->step_id;
				else return false;
				break;
			case 'task':
				$query = $this->ci->db->select('t.task_id')->from('tasks t')->join('steps s', 's.step_id = t.step_id')->join('action a', 'a.action_id = s.action_id')->join('projects p', 'p.project_id = a.project_id')->where('t.task_key', $object_key)->where('p.organization_id', $this->ci->auth->user()->current_organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->task_id;
				else return false;
				break;
			default:
				return false;
		}
	}

	public function has_permission($object_type, $object_id, $permission_name)
	{
		//get project_id
		switch ($object_type) {
			case 'project':
				$query = $this->ci->db->select('project_id')->from('projects')->where('project_id', $object_id)->get();
				if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
				else return false;
				break;
			case 'action':
				$query = $this->ci->db->select('project_id')->from('actions')->where('action_id', $object_id)->get();
				if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
				else return false;
				break;
			case 'step':
				$query = $this->ci->db->select('a.project_id')->from('actions a')->join('steps s', 'a.action_id = s.action_id')->where('s.step_id', $object_id)->get();
				if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
				else return false;
				break;
			case 'task':
				$query = $this->ci->db->select('a.project_id')->from('actions a')->join('steps s', 'a.action_id = s.action_id')->join('tasks t', 't.step_id = s.step_id')->where('t.task_id', $object_id)->get();
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