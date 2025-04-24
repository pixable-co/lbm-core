<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class UpcomingJobsList {

    public static function init() {
        $self = new self();
        add_shortcode( 'upcoming_jobs_list', array($self, 'upcoming_jobs_list_shortcode') );
    }

    public function upcoming_jobs_list_shortcode() {
        $unique_key = 'upcoming_jobs_list_' . uniqid();
        return '<div class="upcoming_jobs_list" data-key="' . esc_attr($unique_key) . '"></div>';
    }
}
