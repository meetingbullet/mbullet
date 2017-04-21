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
}