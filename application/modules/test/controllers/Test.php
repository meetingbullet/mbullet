<?php defined('BASEPATH') || exit('No direct script access allowed');

class Test extends Front_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->library('users/Auth');
	}

	public function login()
	{
		if ($this->input->post()) {
			$this->auth->login($this->input->post('email'), $this->input->post('password'));
			dump($_SESSION);
		} elseif ($this->input->get()) {
			$this->auth->logout();
		}
		Template::render('login');
	}

	public function restrict() {
		$_SESSION['org_id'] = 2;
		$this->auth->restrict('restrict.access');
		if ($this->auth->has_permission('restrict.access')) {
			echo 'aaaa';die;
		} else {
			echo 'false';die;
		}
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
				$day = trim($this->input->post('no_of_days'));
				if ($day == '') {
					$day = 0;
				}
				$calendarId = $this->input->post('calendar_id');

				$eventOptParams = array(
					// 'maxResults' => 10,
					// 'orderBy' => 'startTime',
					// 'singleEvents' => TRUE,
					'timeMin' => date('c', strtotime('now -' . $day . ' days')),
					'timeMax' => date('c')
				);

				$events = $service->events->listEvents($calendarId, $eventOptParams);
				if (! isset($events->error)) {
					$total_time = 0;
					while (true) {
						foreach ($events->getItems() as $event) {
							$start = strtotime(empty($event->start->dateTime) ? $event->start->date : $event->start->dateTime);
							$end = strtotime(empty($event->end->dateTime) ? $event->end->date : $event->end->dateTime);

							$total_time += $end - $start;
						}
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
			$total_time = 0;
		}

		Template::set('total_time', isset($total_time) ? $total_time : null);
		Template::set('calendar_list', $calendar_list);
		Template::render();
	}
}