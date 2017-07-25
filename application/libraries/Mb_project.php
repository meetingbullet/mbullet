<?php defined('BASEPATH') || exit('No direct script access allowed');

class Mb_project
{
	private $ci;
	private $current_user;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->ci->load->library('users/auth');
		$this->current_user = $this->ci->auth->user();
	}

	/**
	/**
	 * Get next key for Project, Action, Meeting, Agenda
	 *
	 * @param string $parent_key
	 * @param string $table Table name (not include prefix)
	 * @return string
	 */
	public function get_next_key($parent_key, $table = "")
	{
		if (empty($table)) {
			$keys = explode('-', $parent_key);
			if (empty($keys)) {
				return false;
			}

			if (count($keys) == 1) {
				$table = 'actions';
			}

			if (count($keys) == 2) {
				$table = 'meetings';
			}

			if (count($keys) == 3) {
				$table = 'agendas';
			}
		} elseif ($table != 'actions' && $table != 'meetings' && $table != 'agendas') {
			return false;
		}

		
		$query = $this->ci->db
		->select('MAX(CAST(REPLACE(`' . rtrim($table, 's') . '_key`, \'' . $parent_key . '-' . '\', \'\') AS UNSIGNED)) AS `last_key`', false)
		->like('`' . rtrim($table, 's') . '_key`', $parent_key . '-', 'after')
		->get($table);

		if ($query->num_rows() > 0) {
			$last_key = $query->row()->last_key;
			return $parent_key . '-' . (empty($last_key) ? 1 : ($last_key + 1));
		} else {
			return false;
		}
	}

	/**
	 * Get object ID from object key (Organization checked)
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param string $object_key
	 * @param int $organization_id If this value is NULL, it will get current organization ID
	 * @return int if found, otherwise return false
	 */
	public function get_object_id($object_type, $object_key, $organization_id = null)
	{
		if (! isset($this->ci->auth->user()->current_organization_id) && is_null($organization_id)) return false;
		elseif (is_null($organization_id)) $organization_id = $this->ci->auth->user()->current_organization_id;

		switch ($object_type) {
			case 'project':
				$query = $this->ci->db->select('project_id')->from('projects')->where('cost_code', $object_key)->where('organization_id', $organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->project_id;
				break;
			case 'action':
				$query = $this->ci->db->select('a.action_id')->from('actions a')->join('projects p', 'p.project_id = a.project_id')->where('a.action_key', $object_key)->where('p.organization_id', $organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->action_id;
				break;
			case 'meeting':
				$query = $this->ci->db->select('s.meeting_id')->from('meetings s')->join('actions a', 'a.action_id = s.action_id')->join('projects p', 'p.project_id = a.project_id')->where('s.meeting_key', $object_key)->where('p.organization_id', $organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->meeting_id;
				break;
			case 'agenda':
				$query = $this->ci->db->select('t.agenda_id')->from('agendas t')->join('meetings s', 's.meeting_id = t.meeting_id')->join('action a', 'a.action_id = s.action_id')->join('projects p', 'p.project_id = a.project_id')->where('t.agenda_key', $object_key)->where('p.organization_id', $organization_id)->get();
				if ($query->num_rows() > 0) return $query->row()->agenda_id;
				break;
			default:
				return false;
		}

		return false;
	}

	/**
	 * Check user has permission or not
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param int $object_id
	 * @param string $permission_name
	 * @return boolean
	 */
	public function has_permission($object_type, $object_id, $permission_name)
	{
		if (isset($this->ci->auth) && $this->ci->auth->is_logged_in()) {
			//get project_id
			switch ($object_type) {
				case 'project':
					$query = $this->ci->db->select('project_id')->from('projects')->where('project_id', $object_id)->get();
					if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
					else return false;
					break;
				case 'action':
					$query = $this->ci->db->select('project_id')->from('actions')->where('action_id', $object_id)->get();
					if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
					else return false;
					break;
				case 'meeting':
					$query = $this->ci->db->select('a.project_id')->from('actions a')->join('meetings s', 'a.action_id = s.action_id')->where('s.meeting_id', $object_id)->get();
					if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
					else return false;
					break;
				case 'agenda':
					$query = $this->ci->db->select('a.project_id')->from('actions a')->join('meetings s', 'a.action_id = s.action_id')->join('agendas t', 't.meeting_id = s.meeting_id')->where('t.agenda_id', $object_id)->get();
					if ($query->num_rows() > 0) $project_id = $query->row()->project_id;
					else return false;
					break;
				default:
					return false;
			}

			//check logged user is project owner ?
			$query = $this->ci->db->select('project_id')->where('project_id', $project_id)->where('owner_id', $this->ci->auth->user()->user_id)->get('projects');
			if ($query->num_rows() > 0) return true;

			//check logged user is project member ?
			$query = $this->ci->db->select('project_id')->where('project_id', $project_id)->where('user_id', $this->ci->auth->user()->user_id)->get('project_members');
			if ($query->num_rows() > 0) return true;

			//check logged user has permission ?
			if (has_permission($permission_name)) return true;
		}
		
		return false;
	}

	/**
	 * Calculate total point used of an object
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param int $object_id
	 * @param int $organization_id If this value is NULL, it will get current organization ID
	 * @return double
	 */
	public function total_point_used($object_type, $object_id, $organization_id = null)
	{
		return $this->total_agenda_point_used($object_type, $object_id, $organization_id) + $this->total_homework_point_used($object_type, $object_id, $organization_id);
	}

	/**
	 * Calculate total agenda point used of an object
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param int $object_id
	 * @param int $organization_id If this value is NULL, it will get current organization ID
	 * @return double
	 */
	private function total_agenda_point_used($object_type, $object_id, $organization_id = null)
	{
		if (! isset($this->ci->auth->user()->current_organization_id) && is_null($organization_id)) return false;
		elseif (is_null($organization_id)) $organization_id = $this->ci->auth->user()->current_organization_id;

		$agenda_point = $this->ci->db->select('IFNULL(SUM((t.finished_on - t.started_on) / 60 * uo.cost_of_time), 0) AS total')
			->from('agendas t')
			->join('agenda_members tm', 'tm.agenda_id = t.agenda_id')
			->join('user_to_organizations uo', 'uo.user_id = tm.user_id')
			->where('uo.organization_id', $organization_id)
			->having('NOT(total IS NULL)');

		switch ($object_type) {
			case 'agenda':
				$agenda_point = $agenda_point->where('t.agenda_id', $object_id)->get();
				if ($agenda_point->num_rows() > 0) return doubleval($agenda_point->row()->total);
				break;
			case 'meeting':
				$agenda_point = $agenda_point->where('t.meeting_id', $object_id)->get();
				if ($agenda_point->num_rows() > 0) return doubleval($agenda_point->row()->total);
				break;
			case 'action':
				$agenda_point = $agenda_point->join('meetings s', 's.meeting_id = t.meeting_id')->where('s.action_id', $object_id)->get();
				if ($agenda_point->num_rows() > 0) return doubleval($agenda_point->row()->total);
				break;
			case 'project':
				$agenda_point = $agenda_point->join('meetings s', 's.meeting_id = t.meeting_id')->join('actions a', 'a.action_id = s.action_id')->where('a.project_id', $object_id)->get();
				if ($agenda_point->num_rows() > 0) return doubleval($agenda_point->row()->total);
				break;
			case 'user':
				$agenda_point = $agenda_point->where('tm.user_id', $object_id)->get();
				if ($agenda_point->num_rows() > 0) return doubleval($agenda_point->row()->total);
				break;
			default:
				return 0;
		}

		return 0;
	}

	/**
	 * Calculate total homework point used of an object
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param int $object_id
	 * @param int $organization_id If this value is NULL, it will get current organization ID
	 * @return double
	 */
	private function total_homework_point_used($object_type, $object_id, $organization_id = null)
	{
		if (! isset($this->ci->auth->user()->current_organization_id) && is_null($organization_id)) return false;
		elseif (is_null($organization_id)) $organization_id = $this->ci->auth->user()->current_organization_id;

		$homework_point = $this->ci->db->select('IFNULL(SUM(hw.time_spent * uo.cost_of_time), 0) AS total')
			->from('homework hw')
			->join('homework_members hwm', 'hwm.homework_id = hw.homework_id')
			->join('user_to_organizations uo', 'uo.user_id = hwm.user_id')
			->where('uo.organization_id', $organization_id)
			->having('NOT(total IS NULL)');

		switch ($object_type) {
			case 'homework':
				$homework_point = $homework_point->where('hw.homework_id', $object_id)->get();
				if ($homework_point->num_rows() > 0) return doubleval($homework_point->row()->total);
				break;
			case 'meeting':
				$homework_point = $homework_point->where('hw.meeting_id', $object_id)->get();
				if ($homework_point->num_rows() > 0) return doubleval($homework_point->row()->total);
				break;
			case 'action':
				$homework_point = $homework_point->join('meetings s', 's.meeting_id = hw.meeting_id')->where('s.action_id', $object_id)->get();
				if ($homework_point->num_rows() > 0) return doubleval($homework_point->row()->total);
				break;
			case 'project':
				$homework_point = $homework_point->join('meetings s', 's.meeting_id = hw.meeting_id')->join('actions a', 'a.action_id = s.action_id')->where('a.project_id', $object_id)->get();
				if ($homework_point->num_rows() > 0) return doubleval($homework_point->row()->total);
				break;
			case 'user':
				$homework_point = $homework_point->where('hwm.user_id', $object_id)->get();
				if ($homework_point->num_rows() > 0) return doubleval($homework_point->row()->total);
				break;
			default:
				return 0;
		}

		return 0;
	}
	/**
	 * Calculate total time/point used of an object
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param int $object_id
	 * @param int $organization_id If this value is NULL, it will get current organization ID
	 * @return double
	 */
	public function total_used($object_type, $object_id, $organization_id = null)
	{
		$agenda = $this->total_agenda_used($object_type, $object_id, $organization_id);
		$homework = $this->total_homework_used($object_type, $object_id, $organization_id);

		return [
			'time' => $agenda->total_time + $homework->total_time,
			'point' => $agenda->total_point + $homework->total_point,
		];
	}

	/**
	 * Calculate total agenda time used of an object
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param int $object_id
	 * @param int $organization_id If this value is NULL, it will get current organization ID
	 * @return double
	 */
	private function total_agenda_used($object_type, $object_id, $organization_id = null)
	{
		if (! isset($this->ci->auth->user()->current_organization_id) && is_null($organization_id)) return false;
		elseif (is_null($organization_id)) $organization_id = $this->ci->auth->user()->current_organization_id;

		$agenda = $this->ci->db
		->select('IFNULL(SUM((t.finished_on - t.started_on) / 60 * uo.cost_of_time), 0) AS total_point, 
		IFNULL(SUM((t.finished_on - t.started_on) / 60), 0) AS total_time')
		->from('agendas t')
		->join('agenda_members tm', 'tm.agenda_id = t.agenda_id')
		->join('user_to_organizations uo', 'uo.user_id = tm.user_id')
		->where('uo.organization_id', $organization_id)
		->having('NOT(total_point IS NULL)')
		->having('NOT(total_time IS NULL)');

		switch ($object_type) {
			case 'agenda':
				$agenda = $agenda->where('t.agenda_id', $object_id)->get();
				if ($agenda->num_rows() > 0) return $agenda->row();
				break;
			case 'meeting':
				$agenda = $agenda->where('t.meeting_id', $object_id)->get();
				if ($agenda->num_rows() > 0) return $agenda->row();
				break;
			case 'action':
				$agenda = $agenda->join('meetings s', 's.meeting_id = t.meeting_id')->where('s.action_id', $object_id)->get();
				if ($agenda->num_rows() > 0) return $agenda->row();
				break;
			case 'project':
				$agenda = $agenda->join('meetings s', 's.meeting_id = t.meeting_id')->join('actions a', 'a.action_id = s.action_id')->where('a.project_id', $object_id)->get();
				if ($agenda->num_rows() > 0) return $agenda->row();
				break;
			case 'user':
				$agenda = $agenda->where('tm.user_id', $object_id)->get();
				if ($agenda->num_rows() > 0) return $agenda->row();
				break;
			default:
				return 0;
		}

		return 0;
	}

	/**
	 * Calculate total homework time used of an object
	 *
	 * @param string $object_type One of following values: project, action, meeting, agenda
	 * @param int $object_id
	 * @param int $organization_id If this value is NULL, it will get current organization ID
	 * @return double
	 */
	private function total_homework_used($object_type, $object_id, $organization_id = null)
	{
		if (! isset($this->ci->auth->user()->current_organization_id) && is_null($organization_id)) return false;
		elseif (is_null($organization_id)) $organization_id = $this->ci->auth->user()->current_organization_id;

		$homework = $this->ci->db
		->select('IFNULL(SUM(hw.time_spent * uo.cost_of_time), 0) AS total_point, 
		IFNULL(SUM(hw.time_spent), 0) AS total_time')
		->from('homework hw')
		->join('homework_members hwm', 'hwm.homework_id = hw.homework_id')
		->join('user_to_organizations uo', 'uo.user_id = hwm.user_id')
		->where('uo.organization_id', $organization_id)
		->having('NOT(total_point IS NULL)')
		->having('NOT(total_time IS NULL)');

		switch ($object_type) {
			case 'homework':
				$homework = $homework->where('hw.homework_id', $object_id)->get();
				if ($homework->num_rows() > 0) return $homework->row();
				break;
			case 'meeting':
				$homework = $homework->where('hw.meeting_id', $object_id)->get();
				if ($homework->num_rows() > 0) return $homework->row();
				break;
			case 'action':
				$homework = $homework->join('meetings s', 's.meeting_id = hw.meeting_id')->where('s.action_id', $object_id)->get();
				if ($homework->num_rows() > 0) return $homework->row();
				break;
			case 'project':
				$homework = $homework->join('meetings s', 's.meeting_id = hw.meeting_id')->join('actions a', 'a.action_id = s.action_id')->where('a.project_id', $object_id)->get();
				if ($homework->num_rows() > 0) return $homework->row();
				break;
			case 'user':
				$homework = $homework->where('hwm.user_id', $object_id)->get();
				if ($homework->num_rows() > 0) return $homework->row();
				break;
			default:
				return 0;
		}

		return 0;
	}

	/**
	 * Send email to project/action/meeting/agenda members
	 *
	 * @param int $object_id - project/action/meeting/agenda id
	 * @param string $object_type - project/action/meeting/agenda
	 * @param string $title - email title
	 * @param string $content - email content maybe a normal string or a template
	 * @param array $exclude - excluded member ids
	 * @param boolean $use_template - use template for $content or not
	 * @param array $data - template data
	 * 				e.g:
	 * 				[
	 * 					'placeholder1' => 'value1' --> normal data
	 * 					'placeholder2' => [
	 * 						'field_name' => 'first_name' --> data from table 'users'' of member, in this case it's the field 'first_name'
	 * 						'user_data' => true --> add this line to recognize this data is from table 'users'
	 * 					]
	 * 				]
	 * @param boolean $override_queue - override email queue or not
	 * @return boolean - true if sent successfully and vice versa
	 */
	public function send_mail_to_members($object_id, $object_type, $title, $content, $exclude = [], $use_template = false, $data = [], $override_queue = false)
	{
		if (empty($object_id) || empty($object_type) || empty($title) || empty($content)) {
			return false;
		}
		$object_type = strtolower($object_type);
		$types = ['project', 'action', 'meeting', 'agenda'];
		if (! in_array($object_type, $types)) {
			return false;
		}
		$this->ci->load->model($object_type . '/' . $object_type . '_model');
		$this->ci->load->model($object_type . '/' . $object_type . '_member_model');
		$object_owner = $this->ci->{$object_type . '_model'}
						->select('u.*, CONCAT(u.first_name, " ", u.last_name) as full_name')
						->join('users u', 'u.user_id = ' . $object_type . 's.owner_id', 'inner')
						->as_array()
						->find($object_id);
		$object_members = $this->ci->{$object_type . '_member_model'}
								->select('u.*, CONCAT(u.first_name, " ", u.last_name) as full_name')
								->join('users u', 'u.user_id = ' . $object_type . '_members.user_id', 'inner')
								->as_array()
								->find_all_by($object_type . '_id', $object_id);

		if (empty($object_owner)) {
			return false;
		}
		$members = [$object_owner];

		if (! empty($object_members)) {
			$members = array_merge($object_members, $members);
		}

		$members = unique_multidim_array($members, 'user_id');
		if (is_array($exclude) && ! empty($exclude)) {
			$filtered_members = array_filter($members, function($v, $k) use ($exclude) {
				if (! in_array($v['user_id'], $exclude)) {
					return true;
				}
				return false;
			}, ARRAY_FILTER_USE_BOTH);
		} else {
			$filtered_members = $members;
		}

		$emails = array_column($filtered_members, 'email');
		$this->ci->load->library('emailer/emailer');
		if (! $use_template) {
			$email_data = [
				'to' => $emails,
				'subject' => $title,
				'message' => $content,
			];
			// dump(1, $email_data); die;
			if (empty($override_queue)) {
				return (boolean) $this->ci->emailer->send($email_data);
			}

			$queue_data = [];
			foreach ($emails as $email) {
				$queue_data[] = [
					'to_email' => $email,
					'subject' => $email_data['subject'],
					'message' => $email_data['message'],
				];
			}
			return (boolean) $this->ci->db->insert_batch('email_queue', $queue_data);
		}
		$this->ci->load->library('parser');

		if (! is_array($data) || empty($data)) {
			return false;
		}

		$send_bulk_mail = true;
		foreach ($data as $field) {
			if (is_array($field)) {
				if (! empty($field['user_data'])) {
					$send_bulk_mail = false;
					break;
				} else {
					return false;
				}
			}
		}

		if ($send_bulk_mail) {
			$email_data = [
				'to' => $emails,
				'subject' => $title,
				'message' => $this->ci->parser->parse_string($content, $data, true)
			];
			// dump(2, $email_data);die;
			if (empty($override_queue)) {
				return (boolean) $this->ci->emailer->send($email_data, $override_queue);
			}

			$queue_data = [];
			foreach ($emails as $email) {
				$queue_data[] = [
					'to_email' => $email,
					'subject' => $email_data['subject'],
					'message' => $email_data['message'],
				];
			}
			return (boolean) $this->ci->db->insert_batch('email_queue', $queue_data);
		}

		$count = 0;
		foreach ($filtered_members as $member) {
			$template_data = [];
			foreach ($data as $placeholder => $field) {
				if (is_array($field)) {
					if ($field['user_data']) {
						if (empty($member[$field['field_name']])) {
							return false;
						}
						$template_data[$placeholder] = $member[$field['field_name']];
					}
				} else {
					$template_data[$placeholder] = $field;
				}
			}

			$email_data = [
				'to' => $member['email'],
				'subject' => $title,
				'message' => $this->ci->parser->parse_string($content, $template_data, true)
			];
			// dump(3 . '.' . ($count + 1), $email_data);
			$sent = $this->ci->emailer->send($email_data, $override_queue);
			if ($sent) {
				$count++;
			}
		}
		return (boolean) $count;
	}
	/**
	 * Send notification mail to project/action/meeting/agenda members after create a project/action/meeting/agenda or change project/action/meeting/agenda status
	 *
	 * @param int $object_id - project/action/meeting/agenda id
	 * @param string $object_type - project/action/meeting/agenda
	 * @param string $current_user_id - id of current user for excluding from email targets
	 * @param string $action_type - to determine which type of email need to send
	 * @return boolean - true if sent successfully and vice versa
	 */
	public function notify_members($object_id, $object_type, $current_user, $action_type = 'insert')
	{
		if (empty($object_id) || empty($object_type) || empty($action_type) || empty($current_user)) {
			return false;
		}
		$current_user_id = $current_user->user_id;

		$action_type = strtolower($action_type);
		$object_type = strtolower($object_type);

		$action_types = ['insert', 'update_status'];
		if (! in_array($action_type, $action_types)) {
			return false;
		}

		$object_types = ['project', 'action', 'meeting', 'agenda'];
		if (! in_array($object_type, $object_types)) {
			return false;
		}

		$this->ci->load->model($object_type . '/' . $object_type . '_model', 'object_model');
		$object = $this->ci->object_model->find($object_id);
		$object_name = $object->name;
		$object_key = $object->{$object_type == 'project' ? 'cost_code' : $object_type . '_key'};

		$template_key = 'NEW_OBJECT';
		if ($action_type == 'update_status') {
			$template_key = 'UPDATE_OBJECT_STATUS';
		}

		$email_template = $this->ci->db->where('email_template_key', $template_key)
								->where('language_code', 'en_US')
								->get('email_templates')->row();
		if (empty($email_template)) {
			return false;
		}

		if ($object_type != 'agenda') {
			$url = site_url($object_type . '/' . $object_key);
		} else {
			$this->ci->load->model('meeting/meeting_model');
			$agenda = $object;
			$meeting_key = $this->ci->meeting_model->get_field($agenda->meeting_id, 'meeting_key');

			$url = site_url('meeting/' . $meeting_key . '?agenda_key=' . $agenda->agenda_key);
		}

		$data = [
			'OBJECT_TYPE_UC' => strtoupper($object_type),
			'OBJECT_TYPE' => ucfirst($object_type),
			'OBJECT_NAME' => $object_name,
			'USER_NAME' => [
				'user_data' => true,
				'field_name' => 'full_name'
			],
			'URL' => $url,
			'LABEL' => site_url($object_type . '/' . $object_key)
		];

		$this->ci->load->library('parser');
		$email_template->email_title = $this->ci->parser->parse_string($email_template->email_title, [
			'OBJECT_TYPE' => ucfirst($object_type)
		], true);

		if ($action_type == 'insert') {
			$this->ci->load->model($object_type . '/' . $object_type . '_member_model', 'object_member_model');
			$object_members = $this->ci->object_member_model
									->select('CONCAT(first_name, " ", last_name) as full_name, email')
									->join('users u', 'u.user_id = ' . $object_type . '_members.user_id', 'left')
									->find_all_by($object_type . '_id', $object_id);

			$members = '';
			if (! empty($object_members)) {
				foreach ($object_members as $key => $member) {
					$members .= $member->full_name . ' (' . $member->email . ')';
					if ($key < (count($object_members) - 1)) {
						$members .= ', ';
					}
				}
			}

			$this->ci->load->model('users/user_model');
			$object_owner = $this->ci->user_model->select('CONCAT(first_name, " ", last_name) as full_name, email')->find($object->owner_id);
			$owner = $object_owner->full_name . ' (' . $object_owner->email . ')';

			$this->ci->load->model('organization/organization_model');
			$object_organization = $this->ci->organization_model->select('name')->organization_model->find($current_user->current_organization_id);
			$organization = $object_organization->name;

			$data['OBJECT_OWNER'] = $owner;
			$data['OBJECT_MEMBERS'] = $members;
			$data['OBJECT_KEY'] = $object_key;
			$data['OBJECT_ORG'] = $organization;
		}

		if ($action_type == 'update_status') {
			$data['STATUS'] = '"' . $object->status . '"';
		}
		return (boolean) $this->send_mail_to_members($object_id, $object_type, $email_template->email_title,
			html_entity_decode($email_template->email_template_content),
			[$current_user_id], true, $data, true);
	}
	/**
	 * Send invitation mail to users after create a meeting
	 *
	 * @param int $object_id - meeting id
	 * @param string $object_type - organization/project/meeting
	 * @param string $current_user - current user info
	 * @param string $user_ids - receiver ids
	 * @return boolean - true if sent successfully and vice versa
	 */
	public function invite_users($object_id, $object_type, $current_user, $user_ids)
	{
		if (empty($object_id) || empty($object_type) || empty($user_ids)) {
			return false;
		}

		$user_ids = array_unique($user_ids);
		$object_type = strtolower($object_type);

		$object_types = ['project', 'meeting'];
		if (! in_array($object_type, $object_types)) {
			return false;
		}
		$this->ci->load->model('users/user_model');
		$users = $this->ci->user_model->select('email, CONCAT(first_name, " ", last_name) as full_name, user_id, invite_code, meeting_id')
									->join($object_type . '_member_invites', $object_type . '_member_invites.invite_email = users.email AND ' . $object_type . '_member_invites.' . $object_type . '_id = "' . $object_id . '"', 'LEFT')
									->where_in('users.user_id', $user_ids)
									->find_all();

		if (empty($users)) {
			return false;
		}

		$this->ci->load->model($object_type . '/' . $object_type . '_model', 'object_model');
		$object = $this->ci->object_model->find($object_id);
		$object_name = $object->name;
		$object_key = $object->{$object_type == 'project' ? 'cost_code' : $object_type . '_key'};

		$template_key = 'INVITE_USER_TO_' . strtoupper($object_type);

		$email_template = $this->ci->db->where('email_template_key', $template_key)
								->where('language_code', 'en_US')
								->get('email_templates')->row();
		if (empty($email_template)) {
			return false;
		}

		$this->ci->load->library('emailer/emailer');
		$this->ci->load->library('parser');

		$email_template->email_title = $this->ci->parser->parse_string($email_template->email_title, [
			'OBJECT_TYPE' => ucfirst($object_type)
		], true);

		$this->ci->load->model($object_type . '/' . $object_type . '_member_model', 'object_member_model');
		$object_members = $this->ci->object_member_model
								->select('CONCAT(first_name, " ", last_name) as full_name, email')
								->join('users u', 'u.user_id = ' . $object_type . '_members.user_id', 'left')
								->find_all_by($object_type . '_id', $object_id);
		
		$members = '';
		if (! empty($object_members)) {
			foreach ($object_members as $key => $member) {
				$members .= $member->full_name . ' (' . $member->email . ')';
				if ($key < (count($object_members) - 1)) {
					$members .= ', ';
				}
			}
		}

		$object_owner = $this->ci->user_model->select('CONCAT(first_name, " ", last_name) as full_name, email')->find($object->owner_id);
		$owner = $object_owner->full_name . ' (' . $object_owner->email . ')';

		$this->ci->load->model('organization/organization_model');
		$object_organization = $this->ci->organization_model->select('name')->organization_model->find($current_user->current_organization_id);
		$organization = $object_organization->name;

		$count = 0;
		foreach ($users as $user) {
			if ($user->user_id != $current_user->user_id) {
				$data = [
					'OBJECT_TYPE' => ucfirst($object_type),
					'OBJECT_NAME' => $object_name,
					'USER_NAME' => $user->full_name,
					'URL' => site_url($object_type . '/' . $object_key),
					'LABEL' => site_url($object_type . '/' . $object_key),
					'OBJECT_OWNER' => $owner,
					'OBJECT_MEMBERS' => $members,
					'OBJECT_KEY' => $object_key,
					'OBJECT_ORG' => $organization
				];

				if ($user->user_id != $object->owner_id) {
					$data['ADDITIONAL_TEXT'] = "You are invited to this " . $object_type . ". Your decision:<a href=" . site_url($object_type . '/invite/' . $user->meeting_id . '/' . $user->invite_code . '/accept') . " style='margin-left: 10px'>Accept</a><a href=" . site_url($object_type . '/invite/' . $user->meeting_id . '/' . $user->invite_code . '/maybe') . " style='margin-left: 10px'>Maybe</a><a href=" . site_url($object_type . '/invite/' . $user->meeting_id . '/' . $user->invite_code . '/decline') . " style='margin-left: 10px'>Decline</a>";
				} else {
					$data['ADDITIONAL_TEXT'] = "You are set to be the owner this " . $object_type . ".";
				}

				$email_data = [
					'to' => $user->email,
					'subject' => $email_template->email_title,
					'message' => $this->ci->parser->parse_string(html_entity_decode($email_template->email_template_content), $data, true),
				];

				$sent = $this->ci->emailer->send($email_data, true);
				if (! empty($sent)) {
					$count++;
				}
			}
		}

		return (boolean) $count;
	}
	/**
	 * Send invitation mail to users after create a meeting
	 *
	 * @param int $object_id - meeting id
	 * @param string $object_type - organization/project/meeting
	 * @param string $current_user - current user info
	 * @param string $emails - receiver email addresses
	 * @return boolean - true if sent successfully and vice versa
	 */
	public function invite_emails($object_id, $object_type, $current_user, $emails)
	{
		if (empty($object_id) || empty($object_type) || empty($emails)) {
			return false;
		}

		$emails = array_unique($emails);
		$object_type = strtolower($object_type);

		$object_types = ['project', 'meeting'];
		if (! in_array($object_type, $object_types)) {
			return false;
		}

		return $this->{'invite_emails_to_' . $object_type}($object_id, $current_user, $emails);
	}

	private function invite_emails_to_meeting($object_id, $current_user, $emails)
	{
		$object_type = 'meeting';
		$this->ci->load->model('users/user_model');
		$users = $this->ci->user_model->select('email, CONCAT(first_name, " ", last_name) as full_name, user_id, invite_code, meeting_id')
									->join($object_type . '_member_invites', $object_type . '_member_invites.invite_email = users.email AND ' . $object_type . '_member_invites.' . $object_type . '_id = "' . $object_id . '"', 'LEFT')
									->where_in('users.email', $emails)
									->find_all();

		if (empty($users)) {
			return false;
		}

		$this->ci->load->model($object_type . '/' . $object_type . '_model', 'object_model');
		$object = $this->ci->object_model->find($object_id);
		$object_name = $object->name;
		$object_key = $object->{$object_type == 'project' ? 'cost_code' : $object_type . '_key'};

		$template_key = 'INVITE_USER_TO_' . strtoupper($object_type);

		$email_template = $this->ci->db->where('email_template_key', $template_key)
								->where('language_code', 'en_US')
								->get('email_templates')->row();
		if (empty($email_template)) {
			return false;
		}

		$this->ci->load->library('emailer/emailer');
		$this->ci->load->library('parser');

		$email_template->email_title = $this->ci->parser->parse_string($email_template->email_title, [
			'OBJECT_TYPE' => ucfirst($object_type)
		], true);

		$this->ci->load->model($object_type . '/' . $object_type . '_member_model', 'object_member_model');
		$object_members = $this->ci->object_member_model
								->select('CONCAT(first_name, " ", last_name) as full_name, email')
								->join('users u', 'u.user_id = ' . $object_type . '_members.user_id', 'left')
								->find_all_by($object_type . '_id', $object_id);
		
		$members = '';
		if (! empty($object_members)) {
			foreach ($object_members as $key => $member) {
				$members .= $member->full_name . ' (' . $member->email . ')';
				if ($key < (count($object_members) - 1)) {
					$members .= ', ';
				}
			}
		}

		$object_owner = $this->ci->user_model->select('CONCAT(first_name, " ", last_name) as full_name, email')->find($object->owner_id);
		$owner = $object_owner->full_name . ' (' . $object_owner->email . ')';

		$this->ci->load->model('organization/organization_model');
		$object_organization = $this->ci->organization_model->select('name')->organization_model->find($current_user->current_organization_id);
		$organization = $object_organization->name;

		$count = 0;
		foreach ($users as $user) {
			if ($user->user_id != $current_user->user_id) {
				$data = [
					'OBJECT_TYPE' => ucfirst($object_type),
					'OBJECT_NAME' => $object_name,
					'USER_NAME' => $user->full_name,
					'URL' => site_url($object_type . '/' . $object_key),
					'LABEL' => site_url($object_type . '/' . $object_key),
					'OBJECT_OWNER' => $owner,
					'OBJECT_MEMBERS' => $members,
					'OBJECT_KEY' => $object_key,
					'OBJECT_ORG' => $organization
				];

				if ($user->user_id != $object->owner_id) {
					$data['ADDITIONAL_TEXT'] = "You are invited to this " . $object_type . ". Your decision:<a href=" . site_url($object_type . '/invite/' . $user->meeting_id . '/' . $user->invite_code . '/accept') . " style='margin-left: 10px'>Accept</a><a href=" . site_url($object_type . '/invite/' . $user->meeting_id . '/' . $user->invite_code . '/maybe') . " style='margin-left: 10px'>Maybe</a><a href=" . site_url($object_type . '/invite/' . $user->meeting_id . '/' . $user->invite_code . '/decline') . " style='margin-left: 10px'>Decline</a>";
				} else {
					$data['ADDITIONAL_TEXT'] = "You are set to be the owner this " . $object_type . ".";
				}

				$email_data = [
					'to' => $user->email,
					'subject' => $email_template->email_title,
					'message' => $this->ci->parser->parse_string(html_entity_decode($email_template->email_template_content), $data, true),
				];

				$sent = $this->ci->emailer->send($email_data, true);
				if (! empty($sent)) {
					$count++;
				}
			}

			$key = array_search($user->email, $emails);
			if ($key !== false) {
				unset($emails[$key]);
			}
		}

		if (! empty($emails)) {
			$this->ci->load->model($object_type . '/' . $object_type . '_member_invite_model', 'object_member_invite_model');

			$guests = $this->ci->object_member_invite_model->where($object_type . '_id', $object_id)->where_in('invite_email', $emails)->find_all();
			if (empty($guests)) {
				$guests = [];
			}

			foreach ($guests as $guest) {
				$data = [
					'OBJECT_TYPE' => ucfirst($object_type),
					'OBJECT_NAME' => $object_name,
					'USER_NAME' => $guest->invite_email,
					'URL' => site_url($object_type . '/' . $object_key),
					'LABEL' => site_url($object_type . '/' . $object_key),
					'OBJECT_OWNER' => $owner,
					'OBJECT_MEMBERS' => $members,
					'OBJECT_KEY' => $object_key,
					'OBJECT_ORG' => $organization
				];

				$data['ADDITIONAL_TEXT'] = "You are invited to this " . $object_type . ". Your decision:<a href=" . site_url($object_type . '/invite/' . $guest->meeting_id . '/' . $guest->invite_code . '/accept') . " style='margin-left: 10px'>Accept</a><a href=" . site_url($object_type . '/invite/' . $guest->meeting_id . '/' . $guest->invite_code . '/maybe') . " style='margin-left: 10px'>Maybe</a><a href=" . site_url($object_type . '/invite/' . $guest->meeting_id . '/' . $guest->invite_code . '/decline') . " style='margin-left: 10px'>Decline</a>";

				$email_data = [
					'to' => $guest->invite_email,
					'subject' => $email_template->email_title,
					'message' => $this->ci->parser->parse_string(html_entity_decode($email_template->email_template_content), $data, true),
				];

				$sent = $this->ci->emailer->send($email_data, true);
				if (! empty($sent)) {
					$count++;
				}
			}
		}

		return (boolean) $count;
	}

	private function invite_emails_to_project($project_id, $current_user, $emails)
	{
		$email_template = $this->ci->db->where('email_template_key', 'INVITE_USER_TO_PROJECT')
								->where('language_code', 'en_US')
								->get('email_templates')->row();
		if (empty($email_template)) {
			return false;
		}

		$this->ci->load->model('project/project_model');
		$this->ci->load->model('project/project_member_model');
		$this->ci->load->model('project/project_member_invite_model');
		$this->ci->load->model('user/user_model');
		$this->ci->load->helper('mb_general_helper');
		$project = $this->ci->project_model->join('users u', 'projects.owner_id = u.user_id')->find($project_id);
		$project_members = $this->ci->project_member_model->join('users u', 'projects.owner_id = u.user_id')->where('project_id', $project_id)->find_all();
		$invitations = $this->ci->project_member_invite_model->where_in('email', $emails)->find_all_by('project_id', $project_id);

		if (empty($project_members)) {
			$project_members = [];
		}

		$members = '<ul>';
		foreach ($project_members as $member) {
			$members .= '<li>' . display_user($member->email, $member->first_name, $member->last_name, $member->avatar) . '</li>';
		}
		$members .= '</ul>';

		$this->ci->load->library('emailer/emailer');
		$this->ci->load->library('parser');

		$count = 0;
		foreach ($invitations as $invitation) {
			
			$data = [
				'SITE_URL' => site_url(),
				'ACCEPT_INVITATION_URL' => site_url('invite/confirm?response=accept'),
				'DECLINE_INVITATION_URL' => site_url('invite/confirm?response=decline'),
				'ORGANIZATION_URL' => site_url(),
				'INVITER_NAME' => $current_user->first_name . ' ' . $current_user->last_name,
				'INVITER_AVATAR_URL' => display_user($current_user->email, $current_user->first_name, $current_user->last_name, $current_user->avatar),
				'INVITER_PROFILE_URL' => '#',
				'INVITEE_EMAIL' => $invitation->invite_email,
				'PROJECT_NAME' => $project->name,
				'PROJECT_CODE' => $project->cost_code,
				'PROJECT_MEMBERS' => $members,
				'OWNER_NAME' => $project->first_name . ' ' . $project->last_name,
				'OWNER_AVATAR_URL' => display_user($project->email, $project->first_name, $project->last_name, $project->avatar)
			];

			$email_data = [
				'to' => $invitation->invite_email,
				'subject' => $email_template->email_title,
				'message' => $this->ci->parser->parse_string(html_entity_decode($email_template->email_template_content), $data, true),
			];

			$sent = $this->ci->emailer->send($email_data, true);
			if (! empty($sent)) {
				$count++;
			}
		}

		return (boolean) $count;
	}

	public function update_parent_objects($object_type, $object_id)
	{
		$types = ['agenda', 'homework', 'meeting', 'action', 'project'];

		if (empty($object_type) || empty($object_id) || ! in_array($object_type, $types)) {
			return false;
		}

		if ($object_type == 'homework') {
			unset($types[0]);
		}

		if ($object_type == 'agenda') {
			unset($types[1]);
		}

		$types = array_values($types);
		$index = array_search($object_type, $types);
		$count = 0;

		for ($i = $index; $i < count($types) - 1; $i++) {
			$this->ci->load->model($types[$i] . '/' . $types[$i] . '_model');
			$object = $this->ci->{$types[$i] . '_model'}->find($object_id);

			if (empty($object)) {
				return false;
			}

			if ($i > 0) {
				$count += $this->ci->{$types[$i] . '_model'}
				->skip_validation(true)
				->update($object_id, ['modified_on' => date('Y-m-d H:i:s')]);
			}

			$object_id = $object->{$types[$i + 1] . '_id'};
		
		}
		
		$this->ci->load->model($types[$i] . '/' . $types[$i] . '_model');
		$count += $this->ci->{$types[$i] . '_model'}
		->skip_validation(true)
		->update($object_id, ['modified_on' => date('Y-m-d H:i:s')]);

		return (boolean) $count;
	}

	
	public function get_project_list()
	{
		$cost_code = '>~<';

		if ($code = explode('-', $this->ci->uri->segment(2))) {
			if (count($code)) {
				$cost_code = $code[0];
			}
		}

		$projects = $this->ci->db
		->select('projects.project_id, name, cost_code, IF(cost_code = "'. $cost_code .'", 1, 0) AS is_selected')
		->join('project_members pm', 'pm.project_id = projects.project_id AND user_id = "'. $this->current_user->user_id .'"', 'LEFT')
		->where("(owner_id = '{$this->current_user->user_id}' OR user_id = '{$this->current_user->user_id}')", null, false)
		->where('organization_id', $this->current_user->current_organization_id)
		->where('projects.status !=', 'archive')
		->get('projects')
		->result();

		$projects = $projects ? $projects : [];
		foreach ($projects as $project) {
			if ($project->is_selected) {
				return [
					'projects' => $projects,
					'current_project_id' => $project->project_id,
					'current_project_name' => $project->name
				];
			}
		}

		return [
			'projects' => $projects,
			'current_project_id' => null,
			'current_project_name' => lang('projects')
		];
	}
}
/**
 * Similar to function array_unique() but apply for multidimensional array
 *
 * @param array $array - input array
 * @param string $key - filter key
 * @return array filtered array
 */
if (! function_exists('unique_multidim_array')) {
	function unique_multidim_array($array, $key)
	{
		$temp_array = array();
		$i = 0;
		$key_array = array();

		foreach($array as $val) {
			if (!in_array($val[$key], $key_array)) {
				$key_array[$i] = $val[$key];
				$temp_array[$i] = $val;
			}
			$i++;
		}
		return $temp_array;
	}
}