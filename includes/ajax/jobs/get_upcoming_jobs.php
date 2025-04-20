<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GetUpcomingJobs {

    public static function init() {
        $self = new self();

        // AJAX for logged-in users
        add_action('wp_ajax_frohub/get_upcoming_jobs', array($self, 'get_upcoming_jobs'));
    }

    public function get_upcoming_jobs() {
        check_ajax_referer('lbm_nonce');

        $engineer_id = isset($_POST['engineerId']) ? sanitize_text_field($_POST['engineerId']) : '';

        if (empty($engineer_id)) {
            wp_send_json_error(['message' => 'Missing engineer ID']);
        }

        $api_url = 'https://www.zohoapis.eu/crm/v7/functions/px_fetchupcomingjobs/actions/execute?auth_type=apikey&zapikey=1003.31030585df90c171ba82fcaa773ae6ce.ef4eb5f7a3d84c75e6a06041e538af0e';

        $response = wp_remote_post($api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode(['engineerId' => $engineer_id]),
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Failed to connect to Zoho API']);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['code']) || $body['code'] !== 'success') {
            wp_send_json_error([
                'message' => 'Zoho API returned an error',
                'data' => $body
            ]);
        }

        $output = json_decode($body['details']['output'], true);
        wp_send_json_success([
            'upcomingJobs' => $output['Upcoming_Jobs'] ?? []
        ]);
    }
}