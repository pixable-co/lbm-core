<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JobsList {

    public static function init() {
        $self = new self();
        add_shortcode( 'jobs_list', array($self, 'jobs_list_shortcode') );
    }

    public function jobs_list_shortcode() {
        $unique_key = 'jobs_list' . uniqid();
        return '<div class="jobs_list" data-key="' . esc_attr($unique_key) . '"></div>';
    }
}