<?php defined('BASEPATH') || exit('No direct script access allowed');

class Cronjob extends MX_Controller {

	public function __construct() {
		parent::__construct();

		if (! is_cli()) {
			show_error('Can not run this page from Web Browser', 500);
		}
	}

	public function index() {
		die('Welcome to Cronjob.');
	}

	public function process_queue_mails() {
		$this->load->library('emailer/emailer');
		$this->emailer->process_queue(6); //limit = 6 to run every minutes
		die('Mail in queue sent.');
	}

	public function sent_meeting_reminder_mails()
	{
		$this->load->model('meeting/meeting_model');
		$members = $this->meeting_model->select('meetings.meeting_id, meetings.name as meeting_name, meetings.meeting_key, att.first_name AS att_first_name, att.last_name AS att_last_name, att.email AS att_email, owner.first_name AS owner_first_name, owner.last_name AS owner_last_name, owner.email AS owner_email,
									IF((SELECT COUNT(*) FROM ' . $this->db->dbprefix('meeting_members') . ' mmb WHERE mmb.meeting_id = ' . $this->db->dbprefix('meetings') . '.meeting_id AND ' . $this->db->dbprefix('meetings') . '.owner_id != mmb.user_id AND mmb.upcoming_reminded = 1) > 0, 1, 0) AS is_owner_got_alert')
									->join('meeting_members mm', 'mm.meeting_id = meetings.meeting_id')
									->join('users att', 'att.user_id = mm.user_id')
									->join('users owner', 'owner.user_id = meetings.owner_id')
									->where('meetings.scheduled_start_time BETWEEN "' . date('Y-m-d H:i:s', strtotime('now')) . '" AND "' . date('Y-m-d H:i:s', strtotime('+ 15 minutes')) . '"')
									->where('(meetings.is_private != "1" OR meetings.is_private IS NULL)')
									->where('mm.user_id != meetings.owner_id')
									->where('mm.upcoming_reminded', 0)
									->order_by('meetings.meeting_id')
									->find_all();

		if (! empty($members)) {
			$template = $this->db->where('email_template_key', 'MEETING_ALERT')
								->where('language_code', 'en_US')
								->get('email_templates')->row();
			if ($template) {
				$this->load->library('emailer/emailer');
				$this->load->library('parser');
				$current_meeting_id = 0;
				$is_first_meeting_member = false;

				foreach ($members as $member) {
					if ($member->meeting_id != $current_meeting_id) {
						$is_first_meeting_member = true;
						$current_meeting_id = $member->meeting_id;
					}

					$email_data = [
						'USER_NAME' => $member->att_first_name . ' ' . $member->att_last_name,
						'MEETING_NAME' => $member->meeting_name,
						'URL' => site_url('meeting/' . $member->meeting_key),
						'LABEL' => site_url('meeting/' . $member->meeting_key)
					];

					$header = $this->load->view('emailer/email/_header', null, true);
					$footer = $this->load->view('emailer/email/_footer', null, true);

					$content = $header;
					$content .= $this->parser->parse_string($template->email_template_content, $email_data, true);
					$content .= $footer;

					$this->emailer->send([
						'to' => $member->att_email,
						'subject' => $template->email_title,
						'message' => $content
					]);

					if ($is_first_meeting_member) {
						$email_data['USER_NAME'] = $member->owner_first_name . ' ' . $member->owner_last_name;

						$content = $header;
						$content .= $this->parser->parse_string($template->email_template_content, $email_data, true);
						$content .= $footer;
	
						$this->emailer->send([
							'to' => $member->owner_email,
							'subject' => $template->email_title,
							'message' => $content
						]);

						$is_first_meeting_member = false;
					}
				}
			}
		}
	}
}