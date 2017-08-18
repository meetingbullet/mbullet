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
	protected $after_insert	= array('set_permission_manage');
	protected $before_update	= array();
	protected $after_update	= array();
	protected $before_find		= array();
	protected $after_find		= array();
	protected $before_delete	= array();
	protected $after_delete	= array('delete_permission_manage');

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
	/**
	 * set permission manage - use for model trigger
	 *
	 * @param [int] $role_id
	 * @return void
	 */
	public function set_permission_manage($role_id) {
		$owner_role = $this->select('role_id')->where('system_default', 0)->where('join_default', 0)->find_by('is_public', 1);
		$this->load->model('admin/permission_manage_model');
		$added = $this->permission_manage_model->insert([
			'role_id' => $owner_role->role_id,
			'manage_role_id' => $role_id
		]);

		if ($added === false) {
			return false;
		}

		return $role_id;
	}
	/**
	 * delete permission manage - use for model trigger
	 *
	 * @param [int] $role_id
	 * @return void
	 */
	public function delete_permission_manage($role_id) {
		$this->load->model('admin/permission_manage_model');
		$deleted = $this->permission_manage_model->where('(role_id = "' . $role_id . '" OR manage_role_id = "' . $role_id . '")')->delete_where([
			'system_default' => 0
		]);

		if ($deleted === false) {
			return false;
		}

		return true;
	}
}
