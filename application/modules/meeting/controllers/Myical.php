<?php defined('BASEPATH') || exit('No direct script access allowed');

class Myical extends Front_Controller
{
	public function __construct()
	{
		$this->load->model('users/user_model');

		$this->load->model('meeting_model');
		$this->load->model('meeting_member_model');
		$this->load->model('meeting_member_invite_model');

		$this->load->helper('date');
	}

	public function index($key = null)
	{
		if (empty($key)) {
			exit;
		}

		$user = $this->user_model->select('users.*, uto.organization_id, uto.role_id, uto.enabled, uto.calendar_uid')
								->join('user_to_organizations uto', 'uto.user_id = users.user_id AND uto.enabled = 1')
								->find_by('uto.calendar_uid', $key);
		if (empty($user)) {
			exit;
		}

		require_once APPPATH . "modules/meeting/libraries/iCalcreator/iCalcreator.php";

		$my_meetings = $this->meeting_model->select('meetings.*, CONCAT(u.first_name, " ", u.last_name) as owner_full_name, u.email as owner_email')
									->join('users u', 'u.user_id = meetings.owner_id')
									->join('actions a', 'a.action_id = meetings.action_id')
									->join('projects p', 'p.project_id = a.project_id')
									->join('meeting_members mb', 'mb.meeting_id = meetings.meeting_id', 'LEFT')
									->where('(meetings.owner_id = "' . $user->user_id . '" OR mb.user_id = "' . $user->user_id . '")')
									->where('organization_id', $user->organization_id)
									->group_by('meetings.meeting_id')
									->find_all();
		$my_meetings = $my_meetings && count($my_meetings) > 0 ? $my_meetings : [];

		$tz = standard_timezone($user->timezone);

		$config = [
			"unique_id" => md5($key . '_' . $user->user_id),
			"TZID" => $tz
		];

		$v = new vcalendar($config);
		$v->setProperty("x-wr-calname", "Meeting Bullet Calendar");
		$v->setProperty("X-WR-TIMEZONE", $tz);

		foreach($my_meetings as $meeting) {
			$meeting_members = $this->meeting_member_model->select('u.*, CONCAT(u.first_name, " ", u.last_name) as member_full_name, mmi.status')
													->join('users u', 'u.user_id = meeting_members.user_id')
													->join('meeting_member_invites mmi', 'mmi.invite_email = u.email')
													->group_by('u.user_id')
													->find_all_by('meeting_members.meeting_id', $meeting->meeting_id);
			$meeting_members = $meeting_members && count($meeting_members) > 0 ? $meeting_members : [];

			$start_time = $meeting->scheduled_start_time;
			if (! empty($start_time)) {
				$start_time_components = [
					'year' => explode('-', explode(' ', $start_time)[0])[0],
					'month' => explode('-', explode(' ', $start_time)[0])[1],
					'day' => explode('-', explode(' ', $start_time)[0])[2],
					'hour' => explode(':', explode(' ', $start_time)[1])[0],
					'min' => explode(':', explode(' ', $start_time)[1])[1],
					'sec' => explode(':', explode(' ', $start_time)[1])[2]
				];

				$end_time = date('Y-m-d H:i:s', strtotime($meeting->scheduled_start_time . ' + ' . $meeting->in . ' ' . $meeting->in_type));
				$end_time_components = [
					'year' => explode('-', explode(' ', $end_time)[0])[0],
					'month' => explode('-', explode(' ', $end_time)[0])[1],
					'day' => explode('-', explode(' ', $end_time)[0])[2],
					'hour' => explode(':', explode(' ', $end_time)[1])[0],
					'min' => explode(':', explode(' ', $end_time)[1])[1],
					'sec' => explode(':', explode(' ', $end_time)[1])[2]
				];
			}

			$vevent = $v->newComponent( "vevent" );
			if (! empty($start_time)) {
				$vevent->setProperty("dtstart", [
					"year" => $start_time_components['year'],
					"month" => $start_time_components['month'],
					"day" => $start_time_components['day'],
					"hour" => $start_time_components['hour'],
					"min" => $start_time_components['min'],
					"sec" => $start_time_components['sec']
				]);

				$vevent->setProperty("dtend", [
					"year" => $end_time_components['year'],
					"month" => $end_time_components['month'],
					"day" => $end_time_components['day'],
					"hour" => $end_time_components['hour'],
					"min" => $end_time_components['min'],
					"sec" => $end_time_components['sec']
				]);
			}

			$vevent->setProperty("summary", $meeting->meeting_key);
			$vevent->setProperty("description", $meeting->name);
			$vevent->setProperty("status", "CONFIRMED");

			$vevent->setProperty("organizer", $meeting->owner_email, [
				"CN" => $meeting->owner_full_name,
			]);

			foreach($meeting_members as $member) {
				$vevent->setProperty( "attendee", $member->email, [
					"cutype" => "INDIVIDUAL",
					"role" => "REQ-PARTICIPANT",
					"PARTSTAT" => $member->status,
					"CN" => $member->member_full_name,
				]);
			}
		}

		$xprops = array( "X-LIC-LOCATION" => $tz );
		iCalUtilityFunctions::createTimezone( $v, $tz, $xprops);

		$ical = $v->createCalendar();
		echo $ical;
		exit;
	}
}