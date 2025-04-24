<?php
namespace LBMCore;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class UpdateNote {

    public static function init() {
        $self = new self();

        // AJAX for logged-in users
        add_action('wp_ajax_frohub/update_note', array($self, 'update_note'));
    }

    public function update_note() {
        check_ajax_referer('lbm_nonce');

        $jobId = sanitize_text_field($_POST['jobId'] ?? '');
        $noteTitle = sanitize_text_field($_POST['noteTitle'] ?? '');
        $noteContent = sanitize_text_field($_POST['noteContent'] ?? '');

        if (!$jobId || !$noteContent) {
            wp_send_json_error(['message' => 'Missing job ID or note content.']);
        }

        $url = "https://www.zohoapis.eu/crm/v7/functions/px_addjobnote/actions/execute?auth_type=apikey&zapikey=1003.31030585df90c171ba82fcaa773ae6ce.ef4eb5f7a3d84c75e6a06041e538af0e";

        $payload = json_encode([
            "jobId" => (int)$jobId,
            "noteTitle" => $noteTitle,
            "noteContent" => $noteContent
        ]);

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $payload,
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'Request failed.', 'details' => $response->get_error_message()]);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['details']['output']) || $body['details']['output'] !== 'Success') {
            wp_send_json_error(['message' => $body['details']['output'] ?? 'Zoho rejected the request.']);
        }

        wp_send_json_success(['message' => 'Note added to CRM.']);
    }
}