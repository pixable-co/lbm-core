<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FutureWorks {

    public static function init() {
        $self = new self();

        // AJAX for logged-in users
        add_action('wp_ajax_lbm/future_works', array($self, 'future_works'));
        // AJAX for non-logged-in users
        add_action('wp_ajax_nopriv_lbm/future_works', array($self, 'future_works'));
    }

    public function future_works() {
        check_ajax_referer('lbm_nonce');

        $job_id      = isset($_POST['jobId']) ? intval($_POST['jobId']) : null;
        $engineer_id = isset($_POST['engineerId']) ? intval($_POST['engineerId']) : null;
        $details     = isset($_POST['details']) ? sanitize_textarea_field($_POST['details']) : '';
        $timestamp   = isset($_POST['timestamp']) ? sanitize_text_field($_POST['timestamp']) : null;

        if (!$job_id || !$engineer_id || empty($details) || !$timestamp) {
            wp_send_json_error(['message' => 'Missing required fields.']);
        }

        $api_url = 'https://www.zohoapis.eu/crm/v7/functions/px_updatejobonfurtherworksrequest/actions/execute?auth_type=apikey&zapikey=1003.31030585df90c171ba82fcaa773ae6ce.ef4eb5f7a3d84c75e6a06041e538af0e';

        $payload = [
            'jobId' => $job_id,
            'engineerId' => $engineer_id,
            'details' => $details,
            'timestamp' => $timestamp
        ];

        $response = wp_remote_post($api_url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Request error', 'error' => $response->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['details'])) {
            wp_send_json_success([
                'message' => 'Further works submitted successfully.',
                'zoho_response' => $body['details']
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Zoho response invalid.',
                'zoho_raw' => $body
            ]);
        }
    }
}