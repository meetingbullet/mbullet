<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_model extends BF_Model
{
    protected $table_name	= 'users';
	protected $key			= 'user_id';
	protected $date_format	= 'datetime';

	protected $log_user 	= false;
	protected $set_created	= true;
	protected $set_modified = false;
	protected $soft_deletes	= true;

	protected $created_field     = 'created_on';
    protected $deleted_field     = 'deleted';

	// Customize the operations of the model without recreating the insert,
    // update, etc. methods by adding the method names to act as callbacks here.
	protected $before_insert 	= array();
	protected $after_insert 	= array();
	protected $before_update 	= array();
	protected $after_update 	= array();
	protected $before_find 	    = array();
	protected $after_find 		= array();
	protected $before_delete 	= array();
	protected $after_delete 	= array();

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
	protected $validation_rules 		= array(
		array(
			'field' => 'email',
			'label' => 'lang:members_field_email',
			'rules' => 'max_length[254]',
		),
		array(
			'field' => 'username',
			'label' => 'lang:members_field_username',
			'rules' => 'trim|max_length[64]',
		),
		array(
			'field' => 'password_hash',
			'label' => 'lang:members_field_password_hash',
			'rules' => 'max_length[60]',
		),
		array(
			'field' => 'first_name',
			'label' => 'lang:members_field_first_name',
			'rules' => 'max_length[255]',
		),
		array(
			'field' => 'last_name',
			'label' => 'lang:members_field_last_name',
			'rules' => 'max_length[255]',
		),
		array(
			'field' => 'reset_hash',
			'label' => 'lang:members_field_reset_hash',
			'rules' => 'max_length[40]',
		),
		array(
			'field' => 'last_login',
			'label' => 'lang:members_field_last_login',
			'rules' => '',
		),
		array(
			'field' => 'last_ip',
			'label' => 'lang:members_field_last_ip',
			'rules' => 'max_length[45]',
		),
		array(
			'field' => 'created_on',
			'label' => 'lang:members_field_created_on',
			'rules' => '',
		),
		array(
			'field' => 'deleted',
			'label' => 'lang:members_field_deleted',
			'rules' => 'max_length[1]',
		),
		array(
			'field' => 'reset_by',
			'label' => 'lang:members_field_reset_by',
			'rules' => 'max_length[10]',
		),
		array(
			'field' => 'timezone',
			'label' => 'lang:members_field_timezone',
			'rules' => 'max_length[40]',
		),
		array(
			'field' => 'language',
			'label' => 'lang:members_field_language',
			'rules' => 'max_length[20]',
		),
		array(
			'field' => 'active',
			'label' => 'lang:members_field_active',
			'rules' => 'max_length[1]',
		),
		array(
			'field' => 'activate_hash',
			'label' => 'lang:members_field_activate_hash',
			'rules' => 'max_length[40]',
		),
		array(
			'field' => 'force_password_reset',
			'label' => 'lang:members_field_force_password_reset',
			'rules' => 'max_length[1]',
		),
		array(
			'field' => 'skype',
			'label' => 'lang:members_field_skype',
			'rules' => 'max_length[255]',
		),
	);
	protected $insert_validation_rules  = array();
	protected $skip_validation 			= false;

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