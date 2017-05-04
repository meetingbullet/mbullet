<?php defined('BASEPATH') || exit('No direct script access allowed');

class Project_model extends BF_Model
{
	protected $table_name	= 'projects';
	protected $key			= 'project_id';
	protected $date_format	= 'datetime';

	protected $log_user	= false;
	protected $set_created	= true;
	protected $set_modified = true;
	protected $soft_deletes	= false;

	protected $created_field	 = 'created_on';

	// Customize the operations of the model without recreating the insert,
	// update, etc. methods by adding the method names to act as callbacks here.
	protected $before_insert	= array();
	protected $after_insert	    = array();
	protected $before_update	= array();
	protected $after_update	    = array();
	protected $before_find		= array();
	protected $after_find		= array();
	protected $before_delete	= array();
	protected $after_delete	    = array();

	// For performance reasons, you may require your model to NOT return the id
	// of the last inserted row as it is a bit of a slow method. This is
	// primarily helpful when running big loops over data.
	protected $return_insert_id = true;

	// The default type for returned row data.
	protected $return_type = 'object';

	// Items that are always removed from data prior to inserts or updates.
	protected $protected_attributes = array();

	// You may need to move certain rules (like required) into the
	// $insert_validation_rules array and out of the standard validation array.
	// That way it is only required during inserts, not updates which may only
	// be updating a portion of the data.
	public $project_validation_rules		= array(
        array(
            'field' => 'name',
            'label' => 'lang:pj_project_name',
            'rules' => 'trim|required|max_length[255]',
        ),
        array(
            'field' => 'cost_code',
            'label' => 'lang:pj_cost_code',
            'rules' => 'trim|required|max_length[64]',
        )
    );

	protected $validation_rules	= array(
		'settings' => array(
			array(
				'field' => 'cost_of_time_1',
				'label' => 'lang:pj_cost_of_time',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'cost_of_time_2',
				'label' => 'lang:pj_cost_of_time',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'cost_of_time_3',
				'label' => 'lang:pj_cost_of_time',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'cost_of_time_4',
				'label' => 'lang:pj_cost_of_time',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'cost_of_time_5',
				'label' => 'lang:pj_cost_of_time',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'value_of_time_1',
				'label' => 'lang:pj_value_of_time',
				'rules' => 'trim|required|numeric'
			),
			array(
				'field' => 'value_of_time_2',
				'label' => 'lang:pj_cost_of_time',
				'rules' => 'trim|required|numeric'
			),
			array(
				'field' => 'value_of_time_3',
				'label' => 'lang:pj_value_of_time',
				'rules' => 'trim|required|numeric'
			),
			array(
				'field' => 'value_of_time_4',
				'label' => 'lang:pj_value_of_time',
				'rules' => 'trim|required|numeric'
			),
			array(
				'field' => 'value_of_time_5',
				'label' => 'lang:pj_value_of_time',
				'rules' => 'trim|required|numeric'
			),
			array(
				'field' => 'project_no_of_hour',
				'label' => 'lang:pj_project_no_of_hour',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'step_owner_no_of_hour',
				'label' => 'lang:pj_step_owner_no_of_hour',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'contributor_no_of_hour',
				'label' => 'lang:pj_contributor_no_of_hour',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'project_total_cost',
				'label' => 'lang:pj_project_total_cost',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'step_owner_total_cost',
				'label' => 'lang:pj_step_owner_total_cost',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'contributor_total_cost',
				'label' => 'lang:pj_contributor_total_cost',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'project_no_of_point',
				'label' => 'lang:pj_project_no_of_point',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'step_owner_no_of_point',
				'label' => 'lang:pj_step_owner_no_of_point',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'contributor_no_of_point',
				'label' => 'lang:pj_contributor_no_of_point',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'project_min_ratio',
				'label' => 'lang:pj_project_min_ratio',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'step_owner_min_ratio',
				'label' => 'lang:pj_step_owner_min_ratio',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'contributor_min_ratio',
				'label' => 'lang:pj_contributor_min_ratio',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'project_min_star',
				'label' => 'lang:pj_project_min_star',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'step_owner_min_star',
				'label' => 'lang:pj_step_owner_min_star',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'contributor_min_star',
				'label' => 'lang:pj_contributor_min_star',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'point_converter',
				'label' => 'lang:pj_point_converter',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'cost',
				'label' => 'lang:pj_cost',
				'rules' => 'trim|numeric'
			),
			array(
				'field' => 'value',
				'label' => 'lang:pj_value',
				'rules' => 'trim|numeric'
			),
		),
    );

	protected $insert_validation_rules  = array();
	protected $skip_validation	= true;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function get_project_by_key($project_key, $current_user, $select = '*', $with_owner = true)
	{
		if (! class_exists('Role_model')) {
			$this->load->model('roles/role_model');
		}
		// check user is organization owner or not
		$is_owner = $this->role_model->where('role_id', $current_user->role_ids[$current_user->current_organization_id])
									->count_by('is_public', 1) == 1 ? true : false;
		// get project id
		if ($is_owner) {
			$this->select($select);

			if ($with_owner === true) {
				$this->join('users u', 'u.user_id = projects.owner_id', 'LEFT');
			}

			$project = $this->where('projects.organization_id', $current_user->current_organization_id)
							->find_by('projects.cost_code', $project_key);
		} else {
			$this->select($select);

			if ($with_owner === true) {
				$this->join('users u', 'u.user_id = projects.owner_id', 'LEFT');
			}

			$project = $this->join('project_members pm', 'pm.project_id = projects.project_id', 'INNER')
							->where('projects.organization_id', $current_user->current_organization_id)
							->where('pm.user_id', $current_user->user_id)
							->find_by('projects.cost_code', $project_key);
		}

		if (! empty($project)) {
			return $project;
		}

		return false;
	}

	public function get_project_id($project_key, $current_user)
	{
		$project = $this->get_project_by_key($project_key, $current_user, 'projects.project_id', false);

		if (! empty($project)) {
			return $project->project_id;
		}

		return false;
	}

	public function count_actions($project_id, $all = true, $user_id = null)
	{
		$this->db->select('COUNT(*) AS total')->from('actions a');
		if (! $all) {
			$this->db->join('action_members am', 'am.action_id = a.action_id', 'LEFT')
					->where('(am.user_id = \'' . $user_id . '\' OR a.owner_id = \'' . $user_id . '\')');
		}
		$query = $this->db->where('a.project_id', $project_id)
						->group_by('a.action_id')
						->get();
		if ($query->num_rows() > 0) {
			return $query->row()->total;
		} else {
			return 0;
		}
	}

	public function count_steps($project_id, $all = true, $user_id = null)
	{
		$this->db->select('COUNT(*) AS total')->from('steps s');
		if (! $all) {
			$this->db->join('step_members sm', 'sm.step_id = s.step_id', 'LEFT')
					->where('(sm.user_id = \'' . $user_id . '\' OR s.owner_id = \'' . $user_id . '\')');
		}
		$query = $this->db->join('actions a', 'a.action_id = s.action_id', 'LEFT')
						->where('a.project_id', $project_id)
						->group_by('s.step_id')
						->get();
		if ($query->num_rows() > 0) {
			return $query->row()->total;
		} else {
			return 0;
		}
	}

	public function count_tasks($project_id, $all = true, $user_id = null)
	{
		$this->db->select('COUNT(*) AS total')->from('tasks t');
		if (! $all) {
			$this->db->join('task_members tm', 'tm.task_id = t.task_id', 'LEFT')
					->where('(tm.user_id = \'' . $user_id . '\' OR t.owner_id = \'' . $user_id . '\')');
		}
		$query = $this->db->join('steps s', 't.step_id = s.step_id', 'LEFT')
						->join('actions a', 'a.action_id = s.action_id', 'LEFT')
						->where('a.project_id', $project_id)
						->group_by('t.task_id')
						->get();
		if ($query->num_rows() > 0) {
			return $query->row()->total;
		} else {
			return 0;
		}
	}

	public function get_actions($project_id, $limit, $offset, $all = true, $user_id = null, $select = 'a.action_key, a.name, a.status, a.action_id, a.point_value')
	{
		$this->db->select($select)->from('actions a');
		if (! $all) {
			$this->db->join('action_members am', 'am.action_id = a.action_id', 'LEFT')
					->where('(am.user_id = \'' . $user_id . '\' OR a.owner_id = \'' . $user_id . '\')');
		}
		$query = $this->db->where('a.project_id', $project_id)
						->group_by('a.action_id')
						->order_by('a.created_on', 'DESC')
						->limit($limit, $offset)
						->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return [];
		}
	}

	public function get_steps($project_id, $limit, $offset, $all = true, $user_id = null, $select = 'a.action_key, s.step_key, s.name, s.status, s.step_id')
	{
		$this->db->select($select)->from('steps s');
		if (! $all) {
			$this->db->join('step_members sm', 'sm.step_id = s.step_id', 'LEFT')
					->where('(sm.user_id = \'' . $user_id . '\' OR s.owner_id = \'' . $user_id . '\')');
		}
		$query = $this->db->join('actions a', 'a.action_id = s.action_id', 'LEFT')
						->where('a.project_id', $project_id)
						->group_by('s.step_id')
						->order_by('s.created_on', 'DESC')
						->limit($limit, $offset)
						->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return [];
		}
	}

	public function get_tasks($project_id, $limit, $offset, $all = true, $user_id = null, $select = 'a.action_key, s.step_key, t.task_key, t.name, t.status')
	{
		$this->db->select($select)->from('tasks t');
		if (! $all) {
			$this->db->join('task_members tm', 'tm.task_id = t.task_id', 'LEFT')
					->where('(tm.user_id = \'' . $user_id . '\' OR t.owner_id = \'' . $user_id . '\')');
		}
		$query = $this->db->join('steps s', 's.step_id = t.step_id', 'LEFT')
						->join('actions a', 'a.action_id = s.action_id', 'LEFT')
						->where('a.project_id', $project_id)
						->group_by('t.task_id')
						->order_by('t.created_on', 'DESC')
						->limit($limit, $offset)
						->get();
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return [];
		}
	}
}
