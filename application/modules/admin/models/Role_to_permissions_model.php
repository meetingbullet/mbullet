<?php defined('BASEPATH') || exit('No direct script access allowed');

class Role_to_permissions_model extends BF_Model
{
	protected $table_name = 'role_to_permissions';
	protected $key = 'role_id';
	protected $date_format = 'datetime';

	// protected $log_user	= true;
	protected $set_created	= false;


	// protected $log_user	= true;
	// Customize the operations of the model without recreating the insert,
	// update, etc. methods by adding the method names to act as callbacks here.
	protected $before_insert	= array();
	protected $after_insert	= array();
	protected $before_update	= array();
	protected $after_update	= array();
	protected $before_find		= array();
	protected $after_find		= array();
	protected $before_delete	= array();
	protected $after_delete	= array();

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
	protected $insert_validation_rules  = array();
	protected $skip_validation			= false;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function get_role_to_permission_relation ($organization_id) {
		return $this->select('role_id, permission_id')
					->where('role_id in (select `role_id` from `mb_roles` `r` where `r`.`organization_id` = ' . $organization_id . ' or (organization_id IS NULL AND is_public = 1))')
					->find_all();
	}

	public function delete_role_to_permission ($organization_id) {
		$this->delete_where('role_id in (select `role_id` from `mb_roles` `r` where `r`.`organization_id` = ' . $organization_id . ' or (organization_id IS NULL AND is_public = 1))');
	}

}