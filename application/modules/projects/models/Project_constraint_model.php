<?php defined('BASEPATH') || exit('No direct script access allowed');

class Project_constraint_model extends BF_Model
{
	protected $table_name	= 'project_constraints';
	protected $key			= 'project_id';
	protected $date_format	= 'datetime';

	protected $log_user	= false;
	protected $set_created	= false;
	protected $set_modified = false;
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
	protected $validation_rules		= array();
	protected $insert_validation_rules  = array(
        array(
            'field' => 'min_hour',
            'label' => 'lang:pj_min_investment_hour',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'max_hour',
            'label' => 'lang:pj_min_investment_hour',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'no_meeting',
            'label' => 'lang:pj_no_meetings',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'no_atendee',
            'label' => 'lang:pj_no_atendees',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'min_roi_rating',
            'label' => 'lang:pj_roi_rating',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'pj_roi_rating',
            'label' => 'lang:pj_min_investment_hour',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'min_period',
            'label' => 'lang:pj_period',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'max_period',
            'label' => 'lang:pj_period',
            'rules' => 'numberic|required|max_length[11]',
        ),
        array(
            'field' => 'peroid_type',
            'label' => 'lang:pj_period',
            'rules' => 'required',
        )
    );
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
}
