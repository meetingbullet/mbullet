<?php defined('BASEPATH') || exit('No direct script access allowed');

class Role_model extends BF_Model
{
	protected $table_name	= 'roles';
	protected $key			= 'role_id';
	protected $date_format	= 'datetime';

	protected $log_user	= true;
	protected $set_created	= true;
	protected $set_modified = true;
	protected $soft_deletes	= false;

	protected $created_field = 'created_on';
	protected $created_by_field = 'created_by';
	protected $modified_field = 'modified_on';
	protected $modified_by_field = 'modified_by';

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
	protected $validation_rules		= array(
		array(
			'field' => 'name',
			'label' => 'lang:rl_name',
			'rules' => 'trim|required|max_length[255]',
		),
		array(
			'field' => 'description',
			'label' => 'lang:rl_description',
			'rules' => 'trim|max_length[255]',
		),
	);
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

	public function get_organization_roles($organization_id, $select = '*')
	{
		return $this->select($select)
					->where('organization_id', $organization_id)
					->or_where('(organization_id IS NULL AND is_public = 1)')
					->order_by('role_id')
					->find_all();
	}

	public function get_organization_default_role($organization_id) {
		$default_role_id = $this->select('role_id')
							->where('organization_id', $organization_id)
							->where('join_default', 1)
							->find_all();
		// $is_update = $this->join('user_to_organizations uto', 'uto.role_id = roles.role_id')
		// 			->update('role_id', $role_id, ['role_id' => $default_role_id->role_id])
		return $default_role_id;

	}
}
