<?php
namespace LBMCore;

use LBMCore\GetJobById;

use LBMCore\GetUpcomingJobs;

use LBMCore\GetPastJobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	public static function init() {
		$self = new self();
		GetPastJobs::init();
		GetUpcomingJobs::init();
		GetJobById::init();
	}
}
