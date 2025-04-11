<?php
namespace LBMCore;

use LBMCore\ViewSingleJob;


use LBMCore\UpcomingJobsList;

use LBMCore\JobsList;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	public static function init() {
		$self = new self();
		JobsList::init();
		UpcomingJobsList::init();
		ViewSingleJob::init();
	}
}
