<?php defined('BASEPATH') || exit('No direct script access allowed');

class Meeting_model extends BF_Model
{
	protected $table_name	= 'meetings';
	protected $key			= 'meeting_id';
	protected $date_format	= 'datetime';

	protected $log_user	= true;
	protected $set_created	= true;
	protected $set_modified = true;
	protected $soft_deletes	= false;

	protected $created_field	 = 'created_on';
	protected $modified_field	 = 'modified_on';
	protected $created_by_field	 = 'created_by';
	protected $modified_by_field	 = 'modified_by';

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
	protected $validation_rules		= array(
		array(
			'field' => 'name',
			'label' => 'lang:st_meeting_name',
			'rules' => 'trim|required|max_length[255]',
		),
		array(
			'field' => 'owner_id',
			'label' => 'lang:st_project_id',
			'rules' => 'trim|numeric',
		),
		array(
			'field' => 'team',
			'label' => 'lang:st_team_member',
			'rules' => 'trim|required',
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

	public function get_meeting_by_key($meeting_key, $organization_id, $select = '*', $with_owner = true)
	{
		$this->select($select);
		if ($with_owner) {
			$this->join('users u', 'u.user_id = meetings.owner_id', 'left');
		}

		$meeting = $this->join('actions a', 'a.action_id = meetings.action_id', 'inner')
					->join('projects p', 'p.project_id = a.project_id', 'inner')
					->where('p.organization_id', $organization_id)
					->find_by('meetings.meeting_key', $meeting_key);

        if (! empty($meeting)) {
            return $meeting;
        }

        return false;
	}
	
    public function get_meeting_id($meeting_key, $organization_id)
    {
		$meeting = $this->get_meeting_by_key($meeting_key, $organization_id, 'meetings.meeting_id');

        if (! empty($meeting)) {
            return $meeting->meeting_id;
        }

        return false;
    }

}