<?php defined('BASEPATH') || exit('No direct script access allowed');

class Action_model extends BF_Model
{
	protected $table_name	= 'actions';
	protected $key			= 'action_id';
	protected $date_format	= 'datetime';

	protected $log_user	= false;
	protected $set_created	= true;
	protected $set_modified = true;
	protected $soft_deletes	= false;

	protected $created_field	 = 'created_on';
	protected $modified_field	 = 'modified_on';

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
	public $validation_rules		= array(
		'create_action' => array(
			array(
				'field' => 'name',
				'label' => 'lang:ac_action_name',
				'rules' => 'trim|required|max_length[255]',
			),
			array(
				'field' => 'project_id',
				'label' => 'lang:ac_project_id',
				'rules' => 'trim|required|numeric',
			),
			array(
				'field' => 'action_key',
				'label' => 'lang:ac_action_key',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'owner_id',
				'label' => 'lang:ac_owner_id',
				'rules' => 'trim|numeric',
			),
			array(
				'field' => 'status',
				'label' => 'lang:ac_action_status',
				'rules' => 'trim',
			),
			array(
				'field' => 'action_type',
				'label' => 'lang:ac_action_type',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'success_condition',
				'label' => 'lang:ac_success_condition',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'point_value',
				'label' => 'lang:ac_point_value',
				'rules' => 'trim|required|numeric',
			)
		)
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
}
