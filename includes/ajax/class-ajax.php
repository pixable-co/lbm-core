<?php
namespace LBMCore;

use LBMCore\FutureWorks;

use LBMCore\TenantNotIn;

use LBMCore\CheckIn;

use LBMCore\ZohoWorkdriveUpload;

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
		ZohoWorkdriveUpload::init();
		CheckIn::init();
		TenantNotIn::init();
		FutureWorks::init();
	}
}
