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
	protected $insert_validation_rules  = array();
	protected $skip_validation	= false;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function get_project_id($project_key, $current_user)
	{
		if (! class_exists('Role_model')) {
			$this->load->model('roles/role_model');
		}
		// check user is organization owner or not
		$is_owner = $this->role_model->where('role_id', $current_user->role_ids[$current_user->current_organization_id])
									->count_by('is_public', 1) == 1 ? true : false;
		// get project id
		if ($is_owner) {
			$project = $this->select('project_id, projects.name')
							->where('projects.organization_id', $current_user->current_organization_id)
							->find_by('projects.cost_code', $project_key);
		} else {
			$project = $this->select('pm.project_id, projects.name')
							->join('project_members pm', 'pm.project_id = projects.projet_id', 'inner')
							->where('projects.organization_id', $current_user->current_organization_id)
							->where('pm.user_id', $current_user->user_id)
							->find_by('projects.cost_code', $project_key);
		}

		if (! empty($project)) {
			return $project->project_id;
		}

		return false;
	}
}
