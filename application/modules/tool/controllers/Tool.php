<?php defined('BASEPATH') || exit('No direct script access allowed');

class Tool extends Front_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('users/Auth');
	}

	public function index()
	{

		$user = $this->auth->user();
		$this->config->load('users/google_api');

		require_once APPPATH . 'modules/users/libraries/google-api-client/vendor/autoload.php';
		$client_id = $this->config->item('client_id');
		$client_secret = $this->config->item('client_secret');

		try {
			$client = new Google_Client();
			$client->setAccessType("offline");
			$client->setClientId($client_id);
			$client->setClientSecret($client_secret);
			$client->refreshToken($user->google_refresh_token);
			$token = $client->getAccessToken();

			$service = new Google_Service_Calendar($client);

			$calendars = $service->calendarList->listCalendarList();
			$calendar_list = [];
			if (! isset($calendars->error)) {
				while (true) {
					foreach ($calendars->getItems() as $calendar) {
						$calendar_list[$calendar->id] = $calendar->summary;
					}
					$pageToken = $calendars->getNextPageToken();
					if ($pageToken) {
						$calOptParams['pageToken'] = $pageToken;
						$calendars = $service->calendarList->listCalendarList($calOptParams);
					} else {
						break;
					}
				}
			}

			if ($this->input->post()) {
				$next_days = trim($this->input->post('no_of_next_days'));
				if ($next_days == '') {
					$next_days = 0;
				}
				$prev_days = trim($this->input->post('no_of_prev_days'));
				if ($prev_days == '') {
					$prev_days = 0;
				}
				$calendarId = $this->input->post('calendar_id');

				$eventOptParams = array(
					// 'maxResults' => 10,
					// 'orderBy' => 'startTime',
					// 'singleEvents' => TRUE,
					'timeMin' => date('c', strtotime('now -' . $prev_days . ' days')),
					'timeMax' => date('c', strtotime('now +' . $next_days . ' days'))
				);

				$event_list = [];
				$events = $service->events->listEvents($calendarId, $eventOptParams);
				if (! isset($events->error)) {
					$total_time = 0;
					while (true) {
						// foreach ($events->getItems() as $event) {
						// 	$start = strtotime(empty($event->start->dateTime) ? $event->start->date : $event->start->dateTime);
						// 	$end = strtotime(empty($event->end->dateTime) ? $event->end->date : $event->end->dateTime);

						// 	$total_time += $end - $start;
						// }
						$event_list = array_merge($event_list, $events->getItems());
						$pageToken = $events->getNextPageToken();
						if ($pageToken) {
							$eventOptParams['pageToken'] = $pageToken;
							$events = $service->events->listEvents($calendarId, $eventOptParams);
						} else {
							break;
						}
					}
				}
			}
		} catch (Exception $e) {
			// $total_time = 0;
		}
//dump($event_list);
		// Template::set('total_time', isset($total_time) ? $total_time : null);
		Template::set('user', $user);
		Template::set('calendar_list', $calendar_list);
		Template::set('event_list', empty($event_list) ? [] : $event_list);
		Template::render();
	}
}