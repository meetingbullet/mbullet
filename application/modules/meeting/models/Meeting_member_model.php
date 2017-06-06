<?php defined('BASEPATH') || exit('No direct script access allowed');

class Meeting_member_model extends BF_Model
{
	protected $table_name	= 'meeting_members';
	protected $key			= 'meeting_id';
	protected $date_format	= 'datetime';

	protected $log_user	= false;
	protected $set_created	= false;
	protected $set_modified = false;
	protected $soft_deletes	= false;

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
	public $validation_rules		= array();
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

	public function get_meeting_member($meeting_id)
	{
		$query = $this->select('uto.user_id, email, first_name, last_name, CONCAT(first_name, " ", last_name) AS full_name,
							avatar, cost_of_time, 
							IF(
								uto.cost_of_time = 1, 
								p.cost_of_time_1,
								IF(
									uto.cost_of_time = 2, 
									p.cost_of_time_2,
									IF(
										uto.cost_of_time = 3, 
										p.cost_of_time_3,
										IF(
											uto.cost_of_time = 4, 
											p.cost_of_time_4,
											p.cost_of_time_5
										)
									)
								)
							) AS cost_of_time_name', false)
							->join('users u', 'meeting_members.user_id = u.user_id')
							->join('meetings s', 'meeting_members.meeting_id = s.meeting_id')
							->join('actions a', 's.action_id = a.action_id')
							->join('projects p', 'p.project_id = a.project_id')
							->join('user_to_organizations uto', 'u.user_id = uto.user_id AND enabled = 1 AND uto.organization_id = ' . $this->auth->user()->current_organization_id)
							->where('meeting_members.meeting_id', $meeting_id)
							->order_by('full_name')
							->order_by('uto.cost_of_time', 'DESC')
							->as_array()
							->find_all();

		return $query ? $query : [];
	}
}
