<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ViewSingleJob {

    public static function init() {
        $self = new self();
        add_shortcode( 'view_single_job', array($self, 'view_single_job_shortcode') );
    }

    public function view_single_job_shortcode() {
        $unique_key = 'view_single_job' . uniqid();
        return '<div class="view_single_job" data-key="' . esc_attr($unique_key) . '"></div>';
    }
}